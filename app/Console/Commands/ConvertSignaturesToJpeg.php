<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConvertSignaturesToJpeg extends Command
{
    protected $signature = 'signatures:convert-to-jpeg';
    protected $description = 'Convert all existing PNG signature data in DB to JPEG (runs only where GD is available)';

    public function handle(): int
    {
        if (!extension_loaded('gd')) {
            $this->error('GD extension is not available. Cannot convert PNG signatures.');
            return self::FAILURE;
        }

        $tables = [
            ['table' => 'request_signatures', 'column' => 'signature_data', 'id' => 'id'],
            ['table' => 'user_signatures',    'column' => 'signature_data', 'id' => 'id'],
        ];

        foreach ($tables as $cfg) {
            $table  = $cfg['table'];
            $column = $cfg['column'];
            $idCol  = $cfg['id'];

            // Check if table exists
            if (!$this->tableExists($table)) {
                $this->line("  Skipping {$table} (table not found)");
                continue;
            }

            $rows = DB::table($table)
                ->whereNotNull($column)
                ->where($column, 'like', 'data:image/png%')
                ->get([$idCol, $column]);

            if ($rows->isEmpty()) {
                $this->info("[{$table}] No PNG signatures found — already up to date.");
                continue;
            }

            $this->info("[{$table}] Converting {$rows->count()} PNG signature(s) to JPEG...");
            $bar = $this->output->createProgressBar($rows->count());
            $bar->start();

            $converted = 0;
            foreach ($rows as $row) {
                try {
                    $dataUri  = $row->$column;
                    $jpeg     = $this->pngDataUriToJpegDataUri($dataUri);
                    if ($jpeg) {
                        DB::table($table)->where($idCol, $row->$idCol)->update([$column => $jpeg]);
                        $converted++;
                    }
                } catch (\Throwable $e) {
                    $this->newLine();
                    $this->warn("  Failed for id={$row->$idCol}: {$e->getMessage()}");
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("[{$table}] Done — {$converted}/{$rows->count()} converted.");
        }

        return self::SUCCESS;
    }

    private function pngDataUriToJpegDataUri(string $dataUri): ?string
    {
        // Strip header: data:image/png;base64,<data>
        $base64 = preg_replace('#^data:image/\w+;base64,#', '', $dataUri);
        $binary = base64_decode($base64);
        if (!$binary) return null;

        // Create image from PNG binary
        $img = @imagecreatefromstring($binary);
        if (!$img) return null;

        // Fill transparent background with white (JPEG has no alpha)
        $width  = imagesx($img);
        $height = imagesy($img);
        $canvas = imagecreatetruecolor($width, $height);
        $white  = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);
        imagecopy($canvas, $img, 0, 0, 0, 0, $width, $height);
        imagedestroy($img);

        // Capture JPEG output
        ob_start();
        imagejpeg($canvas, null, 92);
        $jpeg = ob_get_clean();
        imagedestroy($canvas);

        if (!$jpeg) return null;

        return 'data:image/jpeg;base64,' . base64_encode($jpeg);
    }

    private function tableExists(string $table): bool
    {
        try {
            DB::table($table)->limit(1)->get();
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}

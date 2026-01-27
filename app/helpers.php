<?php

if (!function_exists('formatFileSize')) {
    /**
     * Format file size in human readable format
     *
     * @param int $bytes
     * @return string
     */
    function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

if (!function_exists('generateItemCode')) {
    /**
     * Generate unique item code based on category
     *
     * @param int $categoryId
     * @return string
     */
    function generateItemCode($categoryId)
    {
        $category = \App\Models\Category::find($categoryId);
        
        // Get prefix from category, or use default 'ITM'
        $prefix = $category && $category->code_prefix 
            ? strtoupper($category->code_prefix) 
            : 'ITM';
        
        $currentYear = date('Y');
        
        // Find last item with this prefix and year
        $lastItem = \App\Models\Item::where('code', 'LIKE', "{$prefix}-{$currentYear}-%")
            ->orderByRaw("CAST(SUBSTRING(code, " . (strlen($prefix) + 6) . ") AS UNSIGNED) DESC")
            ->first();
        
        if ($lastItem) {
            // Extract the number part from the code
            $lastNumber = (int) substr($lastItem->code, strlen($prefix) + 6);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        // Format: PREFIX-YYYY-NNN (e.g., MSE-2026-001, KRT-2026-001)
        return sprintf('%s-%s-%03d', $prefix, $currentYear, $nextNumber);
    }
}

if (!function_exists('getUnitOptions')) {
    /**
     * Get list of available unit options for items
     *
     * @return array
     */
    function getUnitOptions()
    {
        return [
            'Botol' => 'Botol',
            'Buah' => 'Buah',
            'Box' => 'Box',
            'Dus' => 'Dus',
            'Dus Besar' => 'Dus Besar',
            'Karton' => 'Karton',
            'Kg' => 'Kg',
            'Liter' => 'Liter',
            'Lusin' => 'Lusin',
            'Meter' => 'Meter',
            'Pack' => 'Pack',
            'Pad' => 'Pad',
            'Pasang' => 'Pasang',
            'Pcs' => 'Pcs',
            'Rim' => 'Rim',
            'Roll' => 'Roll',
            'Sak' => 'Sak',
            'Set' => 'Set',
            'Unit' => 'Unit',
        ];
    }
}

if (!function_exists('formatDateIndo')) {
    /**
     * Format date to Indonesian format with WIB timezone
     *
     * @param mixed $date
     * @param string $format
     * @return string
     */
    function formatDateIndo($date, $format = 'd/m/Y H:i')
    {
        if (!$date) {
            return '-';
        }
        
        // Create Carbon instance and force Asia/Jakarta timezone
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date, 'UTC');
        } else {
            $date = \Carbon\Carbon::parse($date->toDateTimeString(), 'UTC');
        }
        
        // Convert to Jakarta timezone
        $date = $date->timezone('Asia/Jakarta');
        
        return $date->format($format);
    }
}

if (!function_exists('formatDateIndoLong')) {
    /**
     * Format date to Indonesian long format with month name and WIB timezone
     *
     * @param mixed $date
     * @return string
     */
    function formatDateIndoLong($date)
    {
        if (!$date) {
            return '-';
        }
        
        // Create Carbon instance and ensure Asia/Jakarta timezone
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }
        
        // Ensure the date is in Asia/Jakarta timezone
        if ($date->getTimezone()->getName() !== 'Asia/Jakarta') {
            $date = $date->timezone('Asia/Jakarta');
        }
        
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $day = $date->format('d');
        $month = $months[(int)$date->format('m')];
        $year = $date->format('Y');
        $time = $date->format('H:i');
        
        return "{$day} {$month} {$year}, {$time}";
    }
}
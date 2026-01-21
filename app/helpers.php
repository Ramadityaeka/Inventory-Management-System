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
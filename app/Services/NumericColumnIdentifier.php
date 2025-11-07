<?php

namespace App\Services;

use Symfony\Component\DomCrawler\Crawler;

class NumericColumnIdentifier
{
    /**
     * Identify the first numeric column in a table
     *
     * @param Crawler $table
     * @return int|null Column index or null if no numeric column found
     */
    public function identify(Crawler $table): ?int
    {
        $rows = $table->filter('tr');
        
        if ($rows->count() < 2) {
            return null;
        }

        // Get first data row (skip header if exists)
        $firstDataRow = $rows->eq(1);
        $cells = $firstDataRow->filter('td, th');
        $columnCount = $cells->count();

        // Check each column
        for ($colIndex = 0; $colIndex < $columnCount; $colIndex++) {
            if ($this->isNumericColumn($table, $colIndex)) {
                return $colIndex;
            }
        }

        return null;
    }

    /**
     * Check if a column contains mostly numeric values
     *
     * @param Crawler $table
     * @param int $columnIndex
     * @return bool
     */
    protected function isNumericColumn(Crawler $table, int $columnIndex): bool
    {
        $rows = $table->filter('tr');
        $numericCount = 0;
        $totalCount = 0;

        // Check tbody rows first, or all rows if no tbody
        $tbodyRows = $table->filter('tbody tr');
        $rowsToCheck = $tbodyRows->count() > 0 ? $tbodyRows : $rows;
        $startIndex = $tbodyRows->count() > 0 ? 0 : 1; // Start from 0 for tbody, 1 for regular rows
        
        for ($i = $startIndex; $i < $rowsToCheck->count(); $i++) {
            $row = $rowsToCheck->eq($i);
            $cells = $row->filter('td, th');
            
            if ($cells->count() > $columnIndex) {
                $cellValue = trim($cells->eq($columnIndex)->text());
                
                // Extract numeric value using regex
                if (preg_match('/(\d+\.?\d*)/', $cellValue, $matches)) {
                    $numericValue = $matches[1];
                    if (is_numeric($numericValue)) {
                        $numericCount++;
                    }
                }
                $totalCount++;
            }
        }

        // Column is numeric if at least 50% of values are numeric
        return $totalCount > 0 && ($numericCount / $totalCount) >= 0.5;
    }

    /**
     * Extract numeric values from a specific column
     *
     * @param Crawler $table
     * @param int $columnIndex
     * @return array<float>
     */
    public function extractValues(Crawler $table, int $columnIndex): array
    {
        $values = [];

        // Try to get rows from tbody first (data rows)
        $tbodyRows = $table->filter('tbody tr');
        if ($tbodyRows->count() > 0) {
            // Extract from all tbody rows (these are data rows)
            // Note: We process ALL rows in tbody, including first row if it has data
            for ($i = 0; $i < $tbodyRows->count(); $i++) {
                $row = $tbodyRows->eq($i);
                $cells = $row->filter('td, th');
                
                if ($cells->count() > $columnIndex) {
                    $cellValue = trim($cells->eq($columnIndex)->text());
                    
                    // Extract numeric value - handle formats like "1.482 m" or "1.482 m (4 ft 10¼ in)"
                    // First, try to extract the main numeric value before unit
                    if (preg_match('/(\d+\.?\d*)/', $cellValue, $matches)) {
                        $numericValue = $matches[1];
                        if (is_numeric($numericValue)) {
                            $values[] = (float) $numericValue;
                        }
                    }
                }
            }
        } else {
            // No tbody, get all rows and skip header
            $rows = $table->filter('tr');
            
            // Check if first row is a header row (contains only <th> tags, no <td>)
            $firstRow = $rows->first();
            $startIndex = 0;
            
            // If first row has <th> tags and no <td>, it's likely a header
            if ($firstRow->filter('th')->count() > 0 && $firstRow->filter('td')->count() == 0) {
                $startIndex = 1;
            }

            // Extract from all rows starting from startIndex
            for ($i = $startIndex; $i < $rows->count(); $i++) {
                $row = $rows->eq($i);
                $cells = $row->filter('td, th');
                
                if ($cells->count() > $columnIndex) {
                    $cellValue = trim($cells->eq($columnIndex)->text());
                    
                    // Extract numeric value - handle formats like "1.482 m" or "1.482 m (4 ft 10¼ in)"
                    if (preg_match('/(\d+\.?\d*)/', $cellValue, $matches)) {
                        $numericValue = $matches[1];
                        if (is_numeric($numericValue)) {
                            $values[] = (float) $numericValue;
                        }
                    }
                }
            }
        }

        return $values;
    }
}


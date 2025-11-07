<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class GraphGenerator
{
    protected int $width = 800;
    protected int $height = 600;
    protected int $padding = 50;

    /**
     * Generate a graph image from numeric data
     *
     * @param array<float> $data
     * @param string $filename
     * @param string|null $title Optional title to display on the graph
     * @return string Path to the generated image file
     * @throws \InvalidArgumentException
     */
    public function generate(array $data, string $filename, ?string $title = null): string
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('Data array cannot be empty');
        }

        // Create image
        $image = imagecreatetruecolor($this->width, $this->height);
        
        // Colors
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $blue = imagecolorallocate($image, 0, 100, 200);
        $gray = imagecolorallocate($image, 200, 200, 200);

        // Fill background
        imagefill($image, 0, 0, $white);

        // Draw title if provided
        $titleY = 20;
        if ($title !== null && !empty($title)) {
            $this->drawTitle($image, $title, $titleY, $black);
            // Adjust padding to account for title
            $titleY = 40;
        }

        // Calculate graph area
        $graphWidth = $this->width - ($this->padding * 2);
        $graphHeight = $this->height - ($this->padding * 2) - ($titleY > 20 ? 20 : 0);
        
        // Find min and max values
        $minValue = min($data);
        $maxValue = max($data);
        $valueRange = $maxValue - $minValue;
        
        if ($valueRange == 0) {
            $valueRange = 1; // Avoid division by zero
        }

        // Draw axes
        imageline($image, $this->padding, $this->padding, $this->padding, $this->height - $this->padding, $black);
        imageline($image, $this->padding, $this->height - $this->padding, $this->width - $this->padding, $this->height - $this->padding, $black);

        // Draw grid lines and labels
        $this->drawGrid($image, $minValue, $maxValue, $graphHeight, $gray, $black);

        // Draw data points and lines
        $pointCount = count($data);
        $xStep = $graphWidth / max(1, $pointCount - 1);

        for ($i = 0; $i < $pointCount; $i++) {
            $x = $this->padding + ($i * $xStep);
            $normalizedValue = ($data[$i] - $minValue) / $valueRange;
            $y = $this->height - $this->padding - ($normalizedValue * $graphHeight);

            // Draw point
            imagefilledellipse($image, (int)$x, (int)$y, 8, 8, $blue);

            // Draw line to next point
            if ($i < $pointCount - 1) {
                $nextX = $this->padding + (($i + 1) * $xStep);
                $nextNormalizedValue = ($data[$i + 1] - $minValue) / $valueRange;
                $nextY = $this->height - $this->padding - ($nextNormalizedValue * $graphHeight);
                
                imageline($image, (int)$x, (int)$y, (int)$nextX, (int)$nextY, $blue);
            }
        }

        // Save image
        $outputPath = storage_path('app/public/' . $filename);
        $directory = dirname($outputPath);
        
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        imagepng($image, $outputPath);
        imagedestroy($image);

        return $outputPath;
    }

    /**
     * Draw grid lines and value labels
     *
     * @param \GdImage|resource $image
     * @param float $minValue
     * @param float $maxValue
     * @param int $graphHeight
     * @param int $gridColor
     * @param int $textColor
     */
    protected function drawGrid($image, float $minValue, float $maxValue, int $graphHeight, int $gridColor, int $textColor): void
    {
        $gridLines = 5;
        
        for ($i = 0; $i <= $gridLines; $i++) {
            $y = $this->padding + ($i * ($graphHeight / $gridLines));
            $value = $maxValue - (($maxValue - $minValue) * ($i / $gridLines));
            
            // Draw grid line
            imageline($image, $this->padding, (int)$y, $this->width - $this->padding, (int)$y, $gridColor);
            
            // Draw label
            $label = number_format($value, 2);
            imagestring($image, 2, 5, (int)$y - 7, $label, $textColor);
        }
    }

    /**
     * Draw title on the graph
     *
     * @param \GdImage|resource $image
     * @param string $title
     * @param int $y
     * @param int $textColor
     */
    protected function drawTitle($image, string $title, int $y, int $textColor): void
    {
        // Truncate title if too long to fit on image
        $maxWidth = $this->width - 40;
        $title = $this->truncateText($title, $maxWidth);
        
        // Center the title
        $titleWidth = imagefontwidth(5) * strlen($title);
        $x = ($this->width - $titleWidth) / 2;
        
        imagestring($image, 5, (int)$x, $y, $title, $textColor);
    }

    /**
     * Truncate text to fit within max width
     *
     * @param string $text
     * @param int $maxWidth
     * @return string
     */
    protected function truncateText(string $text, int $maxWidth): string
    {
        $fontWidth = imagefontwidth(5);
        $maxChars = floor($maxWidth / $fontWidth);
        
        if (strlen($text) <= $maxChars) {
            return $text;
        }
        
        return substr($text, 0, $maxChars - 3) . '...';
    }
}


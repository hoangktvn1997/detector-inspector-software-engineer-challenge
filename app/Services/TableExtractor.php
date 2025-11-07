<?php

namespace App\Services;

use Symfony\Component\DomCrawler\Crawler;

class TableExtractor
{
    /**
     * Extract all tables from HTML content
     *
     * @param string $html
     * @return array<Crawler>
     */
    public function extract(string $html): array
    {
        $crawler = new Crawler($html);
        $tables = $crawler->filter('table.wikitable, table')->each(function (Crawler $node) {
            return $node;
        });

        return $tables;
    }

    /**
     * Get all rows from a table
     *
     * @param Crawler $table
     * @return array<Crawler>
     */
    public function getRows(Crawler $table): array
    {
        return $table->filter('tr')->each(function (Crawler $node) {
            return $node;
        });
    }

    /**
     * Extract table name/caption from a table with context from headings
     *
     * @param Crawler $table
     * @param Crawler|null $htmlCrawler Optional HTML crawler to search for headings
     * @return string|null
     */
    public function getTableName(Crawler $table, ?Crawler $htmlCrawler = null): ?string
    {
        $name = null;
        $context = null;

        // Try to get caption first (most common in Wikipedia)
        $caption = $table->filter('caption')->first();
        if ($caption->count() > 0) {
            $name = trim($caption->text());
        }

        // Try to get context from heading (h2, h3, h4) above the table
        if ($htmlCrawler !== null) {
            // Get all headings and find the one closest before our table
            $allHeadings = $htmlCrawler->filter('h2, h3, h4, h5');
            $tableNode = $table->getNode(0);
            
            if ($tableNode !== null) {
                $closestHeading = null;
                $closestDistance = PHP_INT_MAX;
                
                foreach ($allHeadings as $heading) {
                    // $heading is already a DOMElement from filter()
                    if ($heading && $tableNode) {
                        // Check if heading comes before table in document order
                        $headingPos = $this->getNodePosition($heading, $htmlCrawler);
                        $tablePos = $this->getNodePosition($tableNode, $htmlCrawler);
                        
                        if ($headingPos < $tablePos && ($tablePos - $headingPos) < $closestDistance) {
                            $closestDistance = $tablePos - $headingPos;
                            $closestHeading = $heading;
                        }
                    }
                }
                
                if ($closestHeading !== null) {
                    $contextText = trim($closestHeading->textContent ?? '');
                    $contextText = preg_replace('/\s*\[edit\]\s*/i', '', $contextText);
                    
                    // Filter out common non-relevant headings
                    $skipHeadings = ['references', 'see also', 'external links', 'notes', 'sources'];
                    $contextLower = strtolower($contextText);
                    
                    if (!in_array($contextLower, $skipHeadings) && strlen($contextText) < 50) {
                        $context = $contextText;
                    }
                }
            }
        }

        // Combine context and caption if both exist
        if ($context !== null && !empty($context) && $name !== null && !empty($name)) {
            // Check if context is already in the name (e.g., "indoor" in caption)
            $contextLower = strtolower($context);
            $nameLower = strtolower($name);
            
            if (strpos($nameLower, $contextLower) === false) {
                // Context not in name, prepend it
                return $context . ' - ' . $name;
            }
        }

        // Return caption if exists
        if ($name !== null && !empty($name)) {
            return $name;
        }

        // Try to get from previous sibling (Wikipedia often puts caption before table)
        $previousSibling = $table->getNode(0)?->previousSibling;
        if ($previousSibling && $previousSibling->nodeName === 'caption') {
            $name = trim($previousSibling->textContent ?? '');
            if (!empty($name)) {
                return $name;
            }
        }

        // Try to get from first header row if no caption
        $firstRow = $table->filter('tr')->first();
        if ($firstRow->count() > 0) {
            $headerCells = $firstRow->filter('th');
            if ($headerCells->count() > 0) {
                // Get first header cell as potential title
                $name = trim($headerCells->first()->text());
                if (!empty($name) && strlen($name) < 100) { // Reasonable length for title
                    return $name;
                }
            }
        }

        return null;
    }

    /**
     * Get approximate position of a node in the document
     *
     * @param \DOMNode $node
     * @param Crawler $crawler
     * @return int
     */
    protected function getNodePosition(\DOMNode $node, Crawler $crawler): int
    {
        $position = 0;
        $allNodes = $crawler->filter('*');
        
        foreach ($allNodes as $index => $n) {
            if ($n->isSameNode($node)) {
                return $index;
            }
        }
        
        return PHP_INT_MAX;
    }
}


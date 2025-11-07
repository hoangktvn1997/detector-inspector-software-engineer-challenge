<?php

namespace App\Console\Commands;

use App\Services\GraphGenerator;
use App\Services\NumericColumnIdentifier;
use App\Services\TableExtractor;
use App\Services\WikipediaPageFetcher;
use Illuminate\Console\Command;

class ExtractWikipediaTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wikipedia:extract {url : The Wikipedia URL to extract data from} {--output= : Output filename for the graph image (default: graph-Y-m-d-His.png)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract numeric data from a Wikipedia table and generate a graph';

    protected WikipediaPageFetcher $pageFetcher;
    protected TableExtractor $tableExtractor;
    protected NumericColumnIdentifier $columnIdentifier;
    protected GraphGenerator $graphGenerator;

    public function __construct(
        WikipediaPageFetcher $pageFetcher,
        TableExtractor $tableExtractor,
        NumericColumnIdentifier $columnIdentifier,
        GraphGenerator $graphGenerator
    ) {
        parent::__construct();
        $this->pageFetcher = $pageFetcher;
        $this->tableExtractor = $tableExtractor;
        $this->columnIdentifier = $columnIdentifier;
        $this->graphGenerator = $graphGenerator;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->argument('url');
        $outputFilename = $this->option('output');

        // Generate default filename with timestamp if not provided
        if (empty($outputFilename)) {
            $timestamp = date('Y-m-d-His');
            $outputFilename = "graph-{$timestamp}.png";
        }

        $this->info("Fetching Wikipedia page: {$url}");

        try {
            // Step 1: Fetch the Wikipedia page
            $html = $this->pageFetcher->fetch($url);
            $this->info('pageFetcher: Page fetched successfully');

            // Step 2: Extract tables
            $tables = $this->tableExtractor->extract($html);
            
            if (empty($tables)) {
                $this->error('No tables found on the Wikipedia page');
                return Command::FAILURE;
            }

            $this->info('tableExtractor: Found ' . count($tables) . ' table(s)');

            // Create HTML crawler for context search
            $htmlCrawler = new \Symfony\Component\DomCrawler\Crawler($html);

            // Step 3: Find a table with numeric column
            $numericColumnIndex = null;
            $selectedTable = null;
            $tableName = null;

            foreach ($tables as $table) {
                $columnIndex = $this->columnIdentifier->identify($table);
                if ($columnIndex !== null) {
                    $numericColumnIndex = $columnIndex;
                    $selectedTable = $table;
                    // Extract table name with context from headings
                    $tableName = $this->tableExtractor->getTableName($table, $htmlCrawler);
                    break;
                }
            }

            if ($selectedTable === null) {
                $this->error('No numeric column found in any table');
                return Command::FAILURE;
            }

            $this->info("columnIdentifier: Found numeric column at index {$numericColumnIndex}");
            
            if ($tableName !== null) {
                $this->info("tableExtractor: Table name: {$tableName}");
            }

            // Step 4: Extract numeric values
            $values = $this->columnIdentifier->extractValues($selectedTable, $numericColumnIndex);
            
            if (empty($values)) {
                $this->error('No numeric values found in the column');
                return Command::FAILURE;
            }

            $this->info('columnIdentifier: Extracted ' . count($values) . ' numeric values');
            $this->info('  Range: ' . min($values) . ' - ' . max($values));

            // Step 5: Generate graph with table name as title
            $outputPath = $this->graphGenerator->generate($values, $outputFilename, $tableName);
            $this->info("graphGenerator: Graph generated successfully");
            $this->info("  Output: {$outputPath}");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

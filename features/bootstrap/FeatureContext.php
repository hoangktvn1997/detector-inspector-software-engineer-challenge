<?php

use Behat\Behat\Context\Context;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    protected ?string $url = null;
    protected ?string $outputPath = null;
    protected int $exitCode = 0;
    protected string $commandOutput = '';

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        // Bootstrap Laravel application
        require_once __DIR__ . '/bootstrap.php';
    }

    /**
     * @Given I have a Wikipedia URL :url
     */
    public function iHaveAWikipediaUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @Given I have an invalid URL :url
     */
    public function iHaveAnInvalidUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @Given I have a Wikipedia URL that contains no tables
     */
    public function iHaveAWikipediaUrlThatContainsNoTables(): void
    {
        // Use a Wikipedia page that likely has no tables
        $this->url = 'https://donate.wikimedia.org/wiki/FAQ';
    }

    /**
     * @When I run the extractor command with the URL
     */
    public function iRunTheExtractorCommandWithTheUrl(): void
    {
        Storage::fake('public');
        
        $this->exitCode = Artisan::call('wikipedia:extract', [
            'url' => $this->url,
            '--output' => 'test-graph.png',
        ]);
        
        $this->commandOutput = Artisan::output();
    }

    /**
     * @Then the program should fetch the Wikipedia page
     */
    public function theProgramShouldFetchTheWikipediaPage(): void
    {
        if ($this->exitCode !== 0) {
            throw new \Exception("Command failed with exit code {$this->exitCode}. Output: {$this->commandOutput}");
        }
        
        if (strpos($this->commandOutput, 'Page fetched successfully') === false) {
            throw new \Exception("Page was not fetched successfully. Output: {$this->commandOutput}");
        }
    }

    /**
     * @Then the program should find at least one table
     */
    public function theProgramShouldFindAtLeastOneTable(): void
    {
        if (strpos($this->commandOutput, 'Found') === false || strpos($this->commandOutput, 'table') === false) {
            throw new \Exception("No tables found. Output: {$this->commandOutput}");
        }
    }

    /**
     * @Then the program should identify a numeric column in the table
     */
    public function theProgramShouldIdentifyANumericColumnInTheTable(): void
    {
        if (strpos($this->commandOutput, 'Found numeric column') === false) {
            throw new \Exception("Numeric column not identified. Output: {$this->commandOutput}");
        }
    }

    /**
     * @Then the program should extract numeric values from that column
     */
    public function theProgramShouldExtractNumericValuesFromThatColumn(): void
    {
        if (strpos($this->commandOutput, 'Extracted') === false || strpos($this->commandOutput, 'numeric values') === false) {
            throw new \Exception("Numeric values not extracted. Output: {$this->commandOutput}");
        }
    }

    /**
     * @Then the program should generate a graph image file
     */
    public function theProgramShouldGenerateAGraphImageFile(): void
    {
        if (strpos($this->commandOutput, 'Graph generated successfully') === false) {
            throw new \Exception("Graph not generated. Output: {$this->commandOutput}");
        }
    }

    /**
     * @Then the output image file should exist
     */
    public function theOutputImageFileShouldExist(): void
    {
        $outputPath = storage_path('app/public/test-graph.png');
        if (!file_exists($outputPath)) {
            throw new \Exception("Output file does not exist at: {$outputPath}");
        }
    }

    /**
     * @Then the program should handle the error gracefully
     */
    public function theProgramShouldHandleTheErrorGracefully(): void
    {
        // Command should exit with failure code
        if ($this->exitCode === 0) {
            throw new \Exception("Command should have failed but exited with success code");
        }
    }

    /**
     * @Then display an appropriate error message
     */
    public function displayAnAppropriateErrorMessage(): void
    {
        if (empty($this->commandOutput)) {
            throw new \Exception("No error message displayed");
        }
    }

    /**
     * @Then the program should detect that no tables exist
     */
    public function theProgramShouldDetectThatNoTablesExist(): void
    {
        if (strpos($this->commandOutput, 'No tables found') === false) {
            throw new \Exception("Should detect no tables. Output: {$this->commandOutput}");
        }
    }
}

# Solution Documentation

## Overview

This solution implements a Wikipedia table data extractor and graph generator using Laravel framework with PHP. The program takes a Wikipedia URL as input, extracts numeric data from tables on the page, and generates a graph image file.

## Architecture

The solution follows a clean architecture pattern with separation of concerns:

### Service Classes

1. **WikipediaPageFetcher** - Handles HTTP requests to fetch Wikipedia pages with proper User-Agent headers
2. **TableExtractor** - Extracts HTML tables from page content using Symfony DomCrawler, extracts table names with context from headings
3. **NumericColumnIdentifier** - Identifies and extracts numeric columns from tables using regex pattern matching
4. **GraphGenerator** - Generates graph images using PHP GD library with customizable titles

### Command

- **ExtractWikipediaTable** - Artisan command that orchestrates the entire process

## Design Decisions

### Technology Choices

- **Laravel 10**: Chosen for its robust framework features and excellent testing support
- **Symfony DomCrawler**: For reliable HTML parsing and table extraction
- **Guzzle HTTP**: For making HTTP requests to Wikipedia with proper User-Agent headers (required by Wikipedia's robots policy)
- **PHP GD Library**: Built-in PHP extension for graph generation (no external dependencies)
- **Behat**: For BDD (Behavior-Driven Development) testing
- **PHPUnit**: For TDD (Test-Driven Development) unit testing

### Assumptions

1. **Numeric Column Detection**: The solution identifies a column as "numeric" if at least 50% of its values are numeric. This handles cases where headers or occasional non-numeric values exist.

2. **Table Selection**: The solution processes tables in order and selects the **first table** that contains a numeric column. If multiple tables exist on the page, only the first matching table will be processed and graphed.

3. **Numeric Value Extraction**: The solution extracts numeric values by:
   - Using regex pattern matching to extract the first numeric value from cell content
   - Handling formats like "1.482 m (4 ft 10¼ in)" by extracting "1.482"
   - Processing all rows in `<tbody>` (data rows) to ensure no values are missed
   - Converting extracted values to float

4. **Table Name Extraction**: The solution extracts table names with context:
   - Primary: Caption from `<caption>` tag
   - Context: Heading (h2, h3, h4, h5) above the table (e.g., "Indoor", "Outdoor")
   - Combination: If heading context is not already in caption, it's prepended (e.g., "Indoor - Women's high jump world record progression")
   - Fallback: First header cell if no caption exists

5. **Graph Format**: Outputs PNG format images with:
   - Line graph visualization with data points and connecting lines
   - Table name displayed as title at the top (centered)
   - Grid lines and value labels on Y-axis
   - Automatic scaling based on data range

6. **Output Filename**: Default filename format is `graph-{Y-m-d-His}.png` (e.g., `graph-2025-11-07-081139.png`) to prevent overwriting previous outputs. Custom filename can be specified via `--output` option.

7. **Error Handling**: The solution gracefully handles errors at each step and provides informative error messages.

## Testing Approach

### TDD (Test-Driven Development)

Unit tests were written first for each service class:
- `WikipediaPageFetcherTest` - Tests HTTP fetching with mocked responses (3 tests)
- `TableExtractorTest` - Tests HTML table extraction and table name extraction (5 tests)
- `NumericColumnIdentifierTest` - Tests numeric column identification and value extraction (3 tests)
- `GraphGeneratorTest` - Tests graph image generation with and without titles (5 tests)

All tests pass successfully

### BDD (Behavior-Driven Development)

Feature scenarios written in Gherkin syntax:
- Main success scenario: Extract and plot numeric column from Wikipedia page
- Error scenarios: Invalid URL, pages without tables

Step definitions implemented in `FeatureContext` to execute these scenarios. The context includes Laravel application bootstrap to enable use of Artisan commands and Laravel facades within Behat tests.

## How to Run

### Prerequisites

- PHP 8.1 or higher
- Composer
- PHP GD extension (for graph generation)
- Internet connection (to fetch Wikipedia pages)

### Installation

1. Install dependencies:
```bash
composer install
```

2. Copy environment file (if needed):
```bash
cp .env.example .env
php artisan key:generate
```

### Running the Command

```bash
php artisan wikipedia:extract "https://en.wikipedia.org/wiki/Women%27s_high_jump_world_record_progression"
```

With custom output filename:
```bash
php artisan wikipedia:extract "https://en.wikipedia.org/wiki/Women%27s_high_jump_world_record_progression" --output=my-graph.png
```

The output image will be saved to `storage/app/public/` directory.

### Running Tests

**Unit Tests (TDD):**
```bash
php artisan test
```

**BDD Tests:**
```bash
vendor/bin/behat
```

## Example Usage

```bash
# Extract data from the example Wikipedia page
php artisan wikipedia:extract "https://en.wikipedia.org/wiki/Women%27s_high_jump_world_record_progression"

# Output:
# Fetching Wikipedia page: https://en.wikipedia.org/wiki/Women%27s_high_jump_world_record_progression
# pageFetcher: Page fetched successfully
# tableExtractor: Found 6 table(s)
# columnIdentifier: Found numeric column at index 0
# tableExtractor: Table name: Women's high jump indoor world record progression
# columnIdentifier: Extracted 39 numeric values
#   Range: 1.482 - 2.08
# graphGenerator: Graph generated successfully
#   Output: /path/to/storage/app/public/graph-2025-11-07-081139.png
```

**Note:** The output filename includes a timestamp to prevent overwriting. The graph image includes the table name as a title at the top.

## File Structure

```
app/
├── Console/
│   └── Commands/
│       └── ExtractWikipediaTable.php    # Main command
└── Services/
    ├── WikipediaPageFetcher.php          # HTTP fetching
    ├── TableExtractor.php                # HTML table extraction
    ├── NumericColumnIdentifier.php       # Numeric column detection
    └── GraphGenerator.php                 # Graph generation

tests/
├── Unit/
│   └── Services/                         # Unit tests (TDD)
│       ├── WikipediaPageFetcherTest.php
│       ├── TableExtractorTest.php
│       ├── NumericColumnIdentifierTest.php
│       └── GraphGeneratorTest.php

features/
├── wikipedia_table_extractor.feature     # BDD scenarios
└── bootstrap/
    └── FeatureContext.php                # BDD step definitions
```

## Limitations and Future Improvements

1. **Graph Customization**: Currently generates simple line graphs. Could add options for:
   - Bar charts
   - Different colors/styles
   - Custom labels and titles

2. **Multiple Tables**: Currently processes only the first table with numeric data. Future enhancement could add an option to process all tables and generate multiple graph files.

3. **Column Selection**: Could add option to specify which column to use instead of auto-detection.

4. **Error Recovery**: Could add retry logic for network failures.

5. **Caching**: Could cache fetched Wikipedia pages to reduce API calls during development.

## Implementation Details

### Table Name Extraction Strategy

The `TableExtractor::getTableName()` method uses a multi-step approach:

1. **Primary Source**: Caption from `<caption>` tag within the table
2. **Context Enhancement**: Searches for headings (h2-h5) above the table to add context like "Indoor" or "Outdoor"
3. **Smart Combination**: Only adds heading context if it's not already present in the caption
4. **Fallback**: Uses first header cell if no caption exists

This ensures that tables with similar captions (e.g., "Women's high jump world record progression") can be distinguished by their section headings.

### Numeric Value Extraction

The `NumericColumnIdentifier::extractValues()` method:

- Processes all rows in `<tbody>` to ensure completeness
- Uses regex pattern `/(\d+\.?\d*)/` to extract the first numeric value from cell content
- Handles complex formats like "1.482 m (4 ft 10¼ in)" by extracting "1.482"
- Skips header rows automatically by checking for `<thead>` or `<th>` tags

This approach ensures all data rows are processed, including the first row if it contains numeric data.

### Graph Generation

The `GraphGenerator::generate()` method:

- Accepts optional title parameter to display table name
- Automatically truncates long titles to fit image width
- Centers title at the top of the graph
- Adjusts graph area to accommodate title space

## Notes

- The solution follows Laravel best practices with dependency injection
- All services are testable and mockable
- Error handling is comprehensive with informative messages
- Code is well-documented with PHPDoc comments
- Default output filenames include timestamps to prevent overwriting


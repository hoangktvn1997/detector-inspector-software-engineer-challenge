# Detector Inspector Software Engineer Challenge

Wikipedia Table Data Extractor and Graph Generator

## Challenge Description

Please read the [CHALLENGE.md](CHALLENGE.md) file for the complete challenge requirements.

## Solution Overview

This solution implements a Wikipedia table data extractor and graph generator using **Laravel 10** framework with **PHP 8.1+**. The program extracts numeric data from Wikipedia tables and generates graph images.
(You can read the [SOLUTION.md](SOLUTION.md) file for details.

### Key Features

- ✅ Fetches Wikipedia pages via HTTP
- ✅ Extracts HTML tables from pages
- ✅ Automatically identifies numeric columns
- ✅ Extracts table names with context from headings (Indoor/Outdoor, etc.)
- ✅ Generates line graph images (PNG format) with table title
- ✅ Default output filename includes timestamp (graph-Y-m-d-His.png)
- ✅ Comprehensive error handling
- ✅ Full test coverage (TDD + BDD)

## Quick Start

### Prerequisites

- PHP 8.1 or higher
- Composer
- PHP GD extension (for graph generation)
- Internet connection

### Installation

```bash
# Install dependencies
composer install

# Copy environment file (if needed)
cp .env.example .env
php artisan key:generate
```

### Usage

```bash
# Extract data and generate graph (default filename: graph-2025-11-07-081139.png)
php artisan wikipedia:extract "https://en.wikipedia.org/wiki/Women%27s_high_jump_world_record_progression"

# With custom output filename
php artisan wikipedia:extract "https://en.wikipedia.org/wiki/Women%27s_high_jump_world_record_progression" --output=my-graph.png
```

The output image will be saved to `storage/app/public/` directory.

**Note:** The program processes tables in order and generates a graph for the **first table** that contains a numeric column. If multiple tables exist, only the first matching table will be processed.

### Running Tests

**Unit Tests (TDD):**
```bash
php artisan test
```

**BDD Tests:**
```bash
vendor/bin/behat
```

## Documentation

For detailed documentation about the solution, architecture, design decisions, and assumptions, please see [SOLUTION.md](SOLUTION.md).

## Project Structure

```
app/
├── Console/Commands/
│   └── ExtractWikipediaTable.php    # Main Artisan command
└── Services/
    ├── WikipediaPageFetcher.php     # HTTP fetching
    ├── TableExtractor.php           # HTML table extraction
    ├── NumericColumnIdentifier.php  # Numeric column detection
    └── GraphGenerator.php           # Graph generation

tests/
├── Unit/Services/                   # Unit tests (TDD)
└── Feature/                         # Feature tests

features/
├── wikipedia_table_extractor.feature # BDD scenarios
└── bootstrap/
    └── FeatureContext.php           # BDD step definitions
```

## Testing Approach

- **TDD (Test-Driven Development)**: Unit tests written first, then implementation
- **BDD (Behavior-Driven Development)**: Feature scenarios in Gherkin syntax

All tests pass successfully ✅ (18 tests, 28 assertions)

## License

This project is part of a coding challenge and is provided as-is.

<?php

namespace Tests\Unit\Services;

use App\Services\NumericColumnIdentifier;
use App\Services\TableExtractor;
use Tests\TestCase;

class NumericColumnIdentifierTest extends TestCase
{
    /** @test */
    public function it_can_identify_numeric_column_in_table()
    {
        $html = '<html><body>
            <table>
                <tr><td>Name</td><td>Age</td><td>Score</td></tr>
                <tr><td>John</td><td>25</td><td>95.5</td></tr>
                <tr><td>Jane</td><td>30</td><td>88.0</td></tr>
            </table>
        </body></html>';

        $extractor = new TableExtractor();
        $tables = $extractor->extract($html);
        
        $identifier = new NumericColumnIdentifier();
        $columnIndex = $identifier->identify($tables[0]);

        // Should identify column index 1 (Age) or 2 (Score)
        $this->assertIsInt($columnIndex);
        $this->assertGreaterThanOrEqual(0, $columnIndex);
    }

    /** @test */
    public function it_returns_null_when_no_numeric_column_found()
    {
        $html = '<html><body>
            <table>
                <tr><td>Name</td><td>City</td><td>Country</td></tr>
                <tr><td>John</td><td>London</td><td>UK</td></tr>
                <tr><td>Jane</td><td>Paris</td><td>France</td></tr>
            </table>
        </body></html>';

        $extractor = new TableExtractor();
        $tables = $extractor->extract($html);
        
        $identifier = new NumericColumnIdentifier();
        $columnIndex = $identifier->identify($tables[0]);

        $this->assertNull($columnIndex);
    }

    /** @test */
    public function it_can_extract_numeric_values_from_column()
    {
        $html = '<html><body>
            <table>
                <tr><td>Name</td><td>Score</td></tr>
                <tr><td>John</td><td>95.5</td></tr>
                <tr><td>Jane</td><td>88.0</td></tr>
                <tr><td>Bob</td><td>92.3</td></tr>
            </table>
        </body></html>';

        $extractor = new TableExtractor();
        $tables = $extractor->extract($html);
        
        $identifier = new NumericColumnIdentifier();
        $columnIndex = $identifier->identify($tables[0]);
        
        $values = $identifier->extractValues($tables[0], $columnIndex);

        $this->assertCount(3, $values);
        $this->assertEquals(95.5, $values[0]);
        $this->assertEquals(88.0, $values[1]);
        $this->assertEquals(92.3, $values[2]);
    }
}


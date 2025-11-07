<?php

namespace Tests\Unit\Services;

use App\Services\TableExtractor;
use Symfony\Component\DomCrawler\Crawler;
use Tests\TestCase;

class TableExtractorTest extends TestCase
{
    /** @test */
    public function it_can_extract_tables_from_html()
    {
        $html = '<html><body>
            <table><tr><td>Data</td></tr></table>
            <table><tr><td>More Data</td></tr></table>
        </body></html>';

        $extractor = new TableExtractor();
        $tables = $extractor->extract($html);

        $this->assertCount(2, $tables);
        $this->assertInstanceOf(Crawler::class, $tables[0]);
    }

    /** @test */
    public function it_returns_empty_array_when_no_tables_found()
    {
        $html = '<html><body><div>No tables here</div></body></html>';

        $extractor = new TableExtractor();
        $tables = $extractor->extract($html);

        $this->assertIsArray($tables);
        $this->assertEmpty($tables);
    }

    /** @test */
    public function it_can_extract_table_rows()
    {
        $html = '<html><body>
            <table>
                <tr><td>Header1</td><td>Header2</td></tr>
                <tr><td>Data1</td><td>Data2</td></tr>
                <tr><td>Data3</td><td>Data4</td></tr>
            </table>
        </body></html>';

        $extractor = new TableExtractor();
        $tables = $extractor->extract($html);
        
        $rows = $extractor->getRows($tables[0]);
        
        $this->assertCount(3, $rows);
    }

    /** @test */
    public function it_can_extract_table_name_from_caption()
    {
        $html = '<html><body>
            <table>
                <caption>Test Table Name</caption>
                <tr><td>Data</td></tr>
            </table>
        </body></html>';

        $extractor = new TableExtractor();
        $tables = $extractor->extract($html);
        
        $tableName = $extractor->getTableName($tables[0]);
        
        $this->assertEquals('Test Table Name', $tableName);
    }

    /** @test */
    public function it_returns_null_when_no_table_name_found()
    {
        $html = '<html><body>
            <table>
                <tr><td>Data</td></tr>
            </table>
        </body></html>';

        $extractor = new TableExtractor();
        $tables = $extractor->extract($html);
        
        $tableName = $extractor->getTableName($tables[0]);
        
        $this->assertNull($tableName);
    }
}


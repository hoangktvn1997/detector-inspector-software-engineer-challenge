<?php

namespace Tests\Unit\Services;

use App\Services\GraphGenerator;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GraphGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_can_generate_graph_image_from_numeric_data()
    {
        $data = [10, 20, 30, 40, 50];
        $generator = new GraphGenerator();
        
        $outputPath = $generator->generate($data, 'test-graph.png');

        $this->assertFileExists($outputPath);
        $this->assertStringEndsWith('.png', $outputPath);
    }

    /** @test */
    public function it_throws_exception_for_empty_data()
    {
        $generator = new GraphGenerator();

        $this->expectException(\InvalidArgumentException::class);
        $generator->generate([], 'test-graph.png');
    }

    /** @test */
    public function it_can_handle_single_data_point()
    {
        $data = [42];
        $generator = new GraphGenerator();
        
        $outputPath = $generator->generate($data, 'single-point.png');

        $this->assertFileExists($outputPath);
    }

    /** @test */
    public function it_can_generate_graph_with_title()
    {
        $data = [10, 20, 30, 40, 50];
        $generator = new GraphGenerator();
        
        $outputPath = $generator->generate($data, 'test-graph-title.png', 'Test Table Name');

        $this->assertFileExists($outputPath);
        $this->assertStringEndsWith('.png', $outputPath);
    }

    /** @test */
    public function it_can_generate_graph_without_title()
    {
        $data = [10, 20, 30];
        $generator = new GraphGenerator();
        
        $outputPath = $generator->generate($data, 'test-graph-no-title.png', null);

        $this->assertFileExists($outputPath);
    }
}


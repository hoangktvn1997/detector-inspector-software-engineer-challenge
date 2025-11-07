<?php

namespace Tests\Unit\Services;

use App\Services\WikipediaPageFetcher;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Tests\TestCase;

class WikipediaPageFetcherTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_fetch_html_content_from_valid_wikipedia_url()
    {
        $client = Mockery::mock(Client::class);
        $expectedHtml = '<html><body>Test content</body></html>';
        
        $client->shouldReceive('get')
            ->once()
            ->with('https://en.wikipedia.org/wiki/Test', Mockery::on(function ($arg) {
                return is_array($arg) && isset($arg['headers']) && isset($arg['headers']['User-Agent']);
            }))
            ->andReturn(new Response(200, [], $expectedHtml));

        $fetcher = new WikipediaPageFetcher($client);
        $result = $fetcher->fetch('https://en.wikipedia.org/wiki/Test');

        $this->assertEquals($expectedHtml, $result);
    }

    /** @test */
    public function it_throws_exception_for_invalid_url()
    {
        $client = Mockery::mock(Client::class);
        
        $client->shouldReceive('get')
            ->once()
            ->with('https://invalid-url.com', Mockery::on(function ($arg) {
                return is_array($arg) && isset($arg['headers']) && isset($arg['headers']['User-Agent']);
            }))
            ->andThrow(new RequestException('Connection failed', Mockery::mock(\Psr\Http\Message\RequestInterface::class)));

        $fetcher = new WikipediaPageFetcher($client);

        $this->expectException(\Exception::class);
        $fetcher->fetch('https://invalid-url.com');
    }

    /** @test */
    public function it_throws_exception_for_non_200_response()
    {
        $client = Mockery::mock(Client::class);
        
        $client->shouldReceive('get')
            ->once()
            ->with('https://en.wikipedia.org/wiki/NotFound', Mockery::on(function ($arg) {
                return is_array($arg) && isset($arg['headers']) && isset($arg['headers']['User-Agent']);
            }))
            ->andReturn(new Response(404, [], 'Not Found'));

        $fetcher = new WikipediaPageFetcher($client);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to fetch page');
        $fetcher->fetch('https://en.wikipedia.org/wiki/NotFound');
    }
}


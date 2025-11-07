<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class WikipediaPageFetcher
{
    protected Client $client;

    public function __construct(?Client $client = null)
    {
        $this->client = $client ?? new Client([
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'WikipediaTableExtractor/1.0 (contact@example.com)',
            ],
            'allow_redirects' => [
                'max' => 5,
                'strict' => true,
                'referer' => true,
            ],
        ]);
    }

    /**
     * Fetch HTML content from a Wikipedia URL
     *
     * @param string $url
     * @return string
     * @throws \Exception
     */
    public function fetch(string $url): string
    {
        try {
            $response = $this->client->get($url, [
                'headers' => [
                    'User-Agent' => 'WikipediaTableExtractor/1.0 (contact@example.com)',
                ],
            ]);
            
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Failed to fetch page: HTTP ' . $response->getStatusCode());
            }

            return (string) $response->getBody();
        } catch (RequestException $e) {
            throw new \Exception('Failed to fetch Wikipedia page: ' . $e->getMessage(), 0, $e);
        }
    }
}


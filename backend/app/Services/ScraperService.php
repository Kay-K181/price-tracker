<?php

namespace App\Services;

use Illuminate\support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class ScraperService
{
    private function extractProductData(Crawler $crawler, string $url) : array
    {
        return [
            'name' => 'Sample Product',
            'price' => 19.99,
            'currency' => 'GBP',
            'source' => $this->getSourceFromUrl($url),
            'url' => $url,
        ];
    }

    private function getSourceFromUrl(string $url) : string
    {
        if (str_contains($url, 'myprotein')) return 'MyProtein';
        if (str_contains($url, 'bulk.com')) return 'Bulk';
        if (str_contains($url, 'hollandandbarrett')) return 'Holland & Barrett';

        return 'Unknown';
    }
    public function scrapeProduct(string $url)
    {
        $response = Http::get($url);

        if (!$response->successful()) {
            return [
                'success' => false,
                'error' => 'Failed to retrieve the webpage.'
            ];
        }

        $html = $response->body();
        $crawler = new Crawler($html);

        $productData = $this->extractProductData($crawler, $url);

        return [
            'success' => true,
            'data' => $productData
        ];
    }
}

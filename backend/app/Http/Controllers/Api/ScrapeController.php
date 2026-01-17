<?php

namespace App\Http\Controllers\Api;

use App\Services\ScraperService;
use Illuminate\Http\Request;

class ScrapeController
{
    public function __construct(
        private ScraperService $scraperService
    ) {}

    public function scrape(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'url' => 'required|url|max:500'
        ]);

        $url = $validated['url'];

        if (!$this->isSupportedSite($url)) {
            return response()->json([
                    'success' => false,
                    'message' => 'The provided URL is not from a supported site.'
            ],400 );
        }

        $result = $this->scraperService->scrapeProduct($url);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to scrape the product data.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'product' => $result['data'],
            'message' => 'Product data scraped successfully.',
            'product_id' => $result['product_id']

        ]);
    }

    private function isSupportedSite(string $url): bool
    {
        $supportedDomains = [
            'myprotein.com',
            'bulk.com',
            'hollandandbarretts.com'
        ];

        foreach ($supportedDomains as $domain) {
            if (str_contains($url, $domain)) {
                return true;
            }
        }
      return false;
    }
}

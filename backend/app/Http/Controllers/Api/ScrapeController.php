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
            'url' => 'required|url|max:500',
            'force_refresh' => 'sometimes|boolean'
        ]);

        $url = $validated['url'];
        $forceRefresh = $validated['force_refresh'] ?? false;

        if (!$this->isSupportedSite($url)) {
            return response()->json([
                    'success' => false,
                    'message' => 'The provided URL is not from a supported site.'
            ],400 );
        }

        $result = $this->scraperService->scrapeProduct($url, $forceRefresh);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to scrape the product data.'
            ], 500);
        }

        $response = [
            'success' => true,
            'product' => $result['data'],
            'message' => 'Product data scraped successfully.',
        ];

        if ((isset($result['product_id']))) {
            $response['product_id'] = $result['product_id'];
        }

        if (isset($result['from_cache'])) {
            $response['from_cache'] = $result['from_cache'];
            if (isset($result['cached_at'])) {
                $response['cached_at'] = $result['cached_at'];
            }
        }

        return response()->json($response, 201);
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

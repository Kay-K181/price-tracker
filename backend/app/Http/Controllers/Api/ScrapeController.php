<?php

namespace App\Http\Controllers\Api;

use App\Jobs\ScrapeProductJob;
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

        ScrapeProductJob::dispatch($url, $forceRefresh);

        return response()->json([
            'success' => true,
            'message' => 'Scraping job dispatched successfully.',
            'url' => $url,
        ], 202);
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

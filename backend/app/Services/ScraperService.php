<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Product;

class ScraperService
{
    private function extractProductData(Crawler $crawler, string $url): array
    {
        $name = $crawler->filter('#product-title')->count() > 0 ? trim($crawler->filter('#product-title')->text()) : 'Unknown Product';

        $priceText = $crawler->filter('#onetime-price')->count() > 0 ? trim($crawler->filter('#onetime-price')->text()) : '0.00';

        $price = (float) preg_replace('/[^0-9.]/', '', $priceText);

        $imageUrl = $crawler->filter('img.gallery-image')->count() > 0 ? $crawler->filter('img.gallery-image')->attr('src')
            : null;

        return [
            'name' => $name,
            'price' => $price,
            'currency' => 'GBP',
            'source' => $this->getSourceFromUrl($url),
            'image_url' => $imageUrl,
            'url' => $url,
        ];
    }

    private function getSourceFromUrl(string $url): string
    {
        if (str_contains($url, 'myprotein')) return 'MyProtein';
        if (str_contains($url, 'bulk.com')) return 'Bulk';
        if (str_contains($url, 'hollandandbarrett')) return 'Holland & Barrett';

        return 'Unknown';
    }

    public function scrapeProduct(string $url, bool $forceRefresh = false): array
    {
        try {
            $cacheKey = 'product'. md5($url);

            if (!$forceRefresh) {
                $cached = Redis::get($cacheKey);
                if ($cached) {
                    $cachedData = json_decode($cached, true);

                    return [
                        'success' => true,
                        'data' => $cachedData,
                        'from_cache' => true,
                        'cached_at' => Redis::ttl($cacheKey) . ' seconds remaining'
                    ];
                }
            }

            $html = Browsershot::url($url)
                ->setNodeBinary('/usr/bin/node')
                ->setNpmBinary('/usr/bin/npm')
                ->setChromePath('/usr/bin/chromium')
                ->noSandbox()
                ->bodyHtml();

            $crawler = new Crawler($html);

            $productData = $this->extractProductData($crawler, $url);

            $product = Product::updateOrCreate(
                ['url' => $productData['url']],
                $productData
            );

            Redis::setex($cacheKey, 3600, json_encode($productData));

            return [
                'success' => true,
                'data' => $productData,
                'saved_product' => true,
                'product_id' => $product->id
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to scrape product: ' . $e->getMessage()
            ];
        }
    }
}

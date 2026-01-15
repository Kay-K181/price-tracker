<?php

namespace App\Services;

use Spatie\Browsershot\Browsershot;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Product;

class ScraperService
{
    private function extractProductData(Crawler $crawler, string $url) : array
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
    private function getSourceFromUrl(string $url) : string
    {
        if (str_contains($url, 'myprotein')) return 'MyProtein';
        if (str_contains($url, 'bulk.com')) return 'Bulk';
        if (str_contains($url, 'hollandandbarrett')) return 'Holland & Barrett';

        return 'Unknown';
    }
    public function scrapeProduct(string $url)
    {
       try {
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

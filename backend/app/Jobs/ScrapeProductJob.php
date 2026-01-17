<?php

namespace App\Jobs;

use App\Services\ScraperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScrapeProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $url,
        public bool $forceRefresh = false
    ) {}

    public function handle(ScraperService $scraperService): void
    {
        $scraperService->scrapeProduct($this->url, $this->forceRefresh);
    }
}

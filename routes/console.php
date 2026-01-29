<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\SeoSetting;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('seo:init', function () {
    SeoSetting::initializeDefaults();
    $this->info('SEO settings initialized! ' . SeoSetting::count() . ' pages created.');
})->purpose('Initialize default SEO settings');

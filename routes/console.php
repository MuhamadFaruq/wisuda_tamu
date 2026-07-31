<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\GuestReportPdf;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('report:guests-pdf {path=output/pdf/laporan-tamu-wisuda.pdf}', function (GuestReportPdf $report) {
    $path = base_path($this->argument('path'));
    if (! is_dir(dirname($path))) mkdir(dirname($path), 0775, true);
    file_put_contents($path, $report->make()->output());
    $this->info("PDF laporan dibuat: {$path}");
})->purpose('Generate a guest attendance report PDF');

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use \Maatwebsite\Excel\Sheet;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // \Illuminate\Support\Facades\DB::listen(function ($query) {
        //     logger($query->sql);
        // });

        Sheet::macro('setOrientation', function (Sheet $sheet, $orientation) {
            $sheet->getDelegate()->getPageSetup()->setOrientation($orientation);
        });

        Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
            $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
        });

        Inertia::version(function () {
            return md5_file(public_path('mix-manifest.json'));
        });

        // Sheet::macro('fitToPage', function (Sheet $sheet, $scale) {
        //     $sheet->getPageSetup()->setScale($scale);
        // });

        // Sheet::macro('setWrap', function (Sheet $sheet, $wrap) {
        //     $sheet->getStyle('A')->getAlignment()->setWrapText($wrap);
        // });
    }
}

<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| CPC v2.0 — jadwal otomatisasi harian (jam 00:30)
|--------------------------------------------------------------------------
| Expire voucer di akhir periode, Diamond expired → Signature,
| downgrade tier inaktif 12 bulan, reminder perpanjangan 3/1 bulan.
*/
Schedule::command('cpc:process')->dailyAt('00:30');

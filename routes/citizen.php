<?php

use Illuminate\Support\Facades\Route;
use Nawasara\Emergency\Http\Api\CitizenContactController;

/*
|--------------------------------------------------------------------------
| Nomor darurat — TERBUKA, tanpa akun
|--------------------------------------------------------------------------
|
| Sengaja tidak di belakang `api.citizen`. Nomor darurat harus dapat
| dijangkau siapa pun — termasuk orang yang baru memasang aplikasi dan belum
| mendaftar, dan itu justru keadaan yang paling mungkin saat seseorang
| membutuhkannya.
|
| Isinya memang publik: nomor-nomor ini tercetak di brosur dan papan
| pengumuman. Tidak ada data warga di sini.
*/

Route::get('/contacts', CitizenContactController::class)->name('contacts');

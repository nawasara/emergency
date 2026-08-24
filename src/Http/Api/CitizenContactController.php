<?php

namespace Nawasara\Emergency\Http\Api;

use Illuminate\Http\JsonResponse;
use Nawasara\Emergency\Services\EmergencyDirectory;

/**
 * Nomor darurat untuk aplikasi warga.
 *
 * Terbuka tanpa akun — sama seperti peta laporan, dan alasannya lebih kuat
 * lagi di sini: nomor darurat harus dapat dijangkau siapa pun, termasuk orang
 * yang baru memasang aplikasi dan belum sempat mendaftar. Menuntut login
 * sebelum menampilkan nomor pemadam kebakaran adalah kegagalan rancangan.
 *
 * Isinya memang publik — nomor-nomor ini tercetak di brosur dan papan
 * pengumuman.
 */
class CitizenContactController
{
    /**
     * GET /api/v1/emergency/contacts
     *
     * `version` disertakan supaya aplikasi tahu kapan perlu mengunduh ulang.
     * Layar ini WAJIB bekerja tanpa jaringan, jadi aplikasi menyimpan
     * salinannya dan hanya menyegarkan bila penandanya berbeda.
     */
    public function __invoke(EmergencyDirectory $directory): JsonResponse
    {
        return response()->json([
            'data' => $directory->grouped(),
            'meta' => ['version' => $directory->version()],
        ]);
    }
}

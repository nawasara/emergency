<?php

namespace Nawasara\Emergency\Services;

use Nawasara\Emergency\Models\EmergencyContact;

/**
 * Daftar nomor darurat, tersusun sebagaimana ditampilkan aplikasi.
 */
class EmergencyDirectory
{
    /**
     * Nomor aktif, dikelompokkan dan terurut.
     *
     * @return array<int,array{group:string,contacts:array<int,array<string,mixed>>}>
     */
    public function grouped(): array
    {
        return EmergencyContact::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('group')
            // Kelompok diurutkan menurut kemendesakan, bukan abjad — lihat
            // EmergencyContact::GROUP_ORDER.
            ->sortBy(fn ($rows, $group) => EmergencyContact::groupRank((string) $group))
            ->map(fn ($rows, $group) => [
                'group' => (string) $group,
                'contacts' => $rows->map(fn (EmergencyContact $c) => [
                    'name' => $c->name,
                    'number' => $c->number,
                    'icon_name' => $c->icon_name,
                    'description' => $c->description,
                    'primary' => $c->primary,
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * Penanda "berubah atau tidak", untuk aplikasi yang menyimpan salinan.
     *
     * ⚠️ Dihitung dari waktu ubah TERBARU **dan jumlah baris**, bukan salah
     * satunya.
     *
     * Waktu saja tidak cukup: menghapus sebuah nomor tidak mengubah
     * `updated_at` baris mana pun yang tersisa, sehingga aplikasi akan terus
     * menampilkan nomor yang sudah dicabut — dan nomor darurat yang salah
     * lebih berbahaya daripada tidak ada nomor sama sekali.
     *
     * Jumlah saja juga tidak cukup: mengganti satu nomor tanpa menambah baris
     * tidak mengubah jumlahnya.
     *
     * Keduanya bersama menangkap tambah, ubah, dan hapus.
     */
    public function version(): string
    {
        $row = EmergencyContact::active()
            ->selectRaw('COUNT(*) as jumlah, MAX(updated_at) as terbaru')
            ->first();

        $jumlah = (int) ($row->jumlah ?? 0);
        $terbaru = $row->terbaru ?? '0';

        return substr(sha1($jumlah.'|'.$terbaru), 0, 12);
    }
}

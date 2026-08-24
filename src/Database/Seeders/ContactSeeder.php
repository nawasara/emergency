<?php

namespace Nawasara\Emergency\Database\Seeders;

use Illuminate\Database\Seeder;
use Nawasara\Emergency\Models\EmergencyContact;

/**
 * Nomor darurat awal.
 *
 * ⚠️ **Nomor di sini adalah nomor nasional yang berlaku di mana pun** — 112,
 * 110, 113, 118. Nomor LOKAL Ponorogo sengaja tidak ditebak: nomor darurat
 * yang salah lebih berbahaya daripada nomor yang belum ada, karena orang yang
 * meneleponnya sedang dalam keadaan yang tidak memberi kesempatan kedua.
 *
 * Nomor lokal diisi staf lewat panel, dan panelnya ada justru supaya itu
 * tidak menunggu rilis.
 *
 * Dijalankan dengan `updateOrCreate` pada nomor + nama, sehingga aman
 * dijalankan berulang saat deploy dan tidak menimpa suntingan staf pada
 * baris lain.
 */
class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $contacts = [
            // ── Darurat ────────────────────────────────────────────────
            ['Darurat', 'Panggilan Darurat Terpadu', '112', 'shield-alert', true, 1,
                'Satu nomor untuk semua keadaan darurat. Bebas pulsa.'],
            ['Darurat', 'Kepolisian', '110', 'siren', false, 2, null],
            ['Darurat', 'Pemadam Kebakaran', '113', 'flame', false, 3, null],

            // ── Kesehatan ──────────────────────────────────────────────
            ['Kesehatan', 'Ambulans', '118', 'ambulance', true, 1, null],
            ['Kesehatan', 'Layanan Gawat Darurat', '119', 'heart-pulse', false, 2, null],

            // ── Bencana ────────────────────────────────────────────────
            ['Bencana', 'BNPB', '117', 'cloud-lightning', true, 1,
                'Badan Nasional Penanggulangan Bencana.'],

            // ── Layanan Publik ─────────────────────────────────────────
            ['Layanan Publik', 'PLN Gangguan', '123', 'zap', true, 1, null],
        ];

        foreach ($contacts as [$group, $name, $number, $icon, $primary, $order, $desc]) {
            EmergencyContact::updateOrCreate(
                ['number' => $number, 'name' => $name],
                [
                    'group' => $group,
                    'icon_name' => $icon,
                    'primary' => $primary,
                    'sort_order' => $order,
                    'description' => $desc,
                    'is_active' => true,
                ],
            );
        }

        $this->command?->info('  Nomor darurat: '.EmergencyContact::count());
    }
}

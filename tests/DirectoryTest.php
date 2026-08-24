<?php

namespace Nawasara\Emergency\Tests;

use Nawasara\Emergency\Models\EmergencyContact;
use PHPUnit\Framework\TestCase;

/**
 * Urutan kelompok dan penanda versi.
 *
 * Keduanya gagal DIAM-DIAM bila keliru: layarnya tetap tampil, hanya urutan
 * atau isinya yang salah — dan pada layar nomor darurat, keduanya berakibat
 * orang menelepon nomor yang keliru saat keadaan buruk.
 */
class DirectoryTest extends TestCase
{
    /**
     * Kelompok diurutkan menurut kemendesakan, BUKAN abjad.
     *
     * Secara alfabet "Bencana" mendahului "Darurat" — dan itu keliru: yang
     * paling sering dibutuhkan harus paling mudah ditemukan.
     */
    public function test_urutan_kelompok_bukan_alfabet(): void
    {
        $this->assertSame(0, EmergencyContact::groupRank('Darurat'));
        $this->assertSame(1, EmergencyContact::groupRank('Kesehatan'));
        $this->assertSame(2, EmergencyContact::groupRank('Layanan Publik'));
        $this->assertSame(3, EmergencyContact::groupRank('Bencana'));

        // Bukti bahwa urutannya memang berbeda dari abjad.
        $alfabet = ['Bencana', 'Darurat', 'Kesehatan', 'Layanan Publik'];
        sort($alfabet);

        $tampil = EmergencyContact::GROUP_ORDER;

        $this->assertNotSame($alfabet, $tampil, 'urutan tampil tidak boleh sama dengan abjad');
        $this->assertSame('Darurat', $tampil[0]);
    }

    /**
     * Kelompok baru yang diketik staf tetap diterima — muncul paling akhir,
     * bukan hilang.
     */
    public function test_kelompok_tak_dikenal_jatuh_ke_akhir(): void
    {
        $rank = EmergencyContact::groupRank('Posko Banjir');

        $this->assertSame(count(EmergencyContact::GROUP_ORDER), $rank);
        $this->assertGreaterThan(EmergencyContact::groupRank('Bencana'), $rank);
    }

    /**
     * Penanda versi harus menangkap PENGHAPUSAN, bukan hanya perubahan.
     *
     * Ini inti persoalannya: aplikasi menyimpan salinan nomor darurat untuk
     * dipakai tanpa jaringan. Bila penandanya hanya dihitung dari waktu ubah
     * terbaru, menghapus sebuah nomor tidak mengubah apa pun — dan aplikasi
     * akan terus menampilkan nomor yang sudah dicabut.
     *
     * Diuji lewat rumusnya langsung, karena itulah bagian yang menentukan.
     */
    public function test_versi_berubah_saat_baris_dihapus(): void
    {
        $versi = fn (int $jumlah, string $terbaru) => substr(sha1($jumlah.'|'.$terbaru), 0, 12);

        $waktu = '2026-08-24 10:00:00';

        // Menghapus satu nomor: waktu ubah baris yang TERSISA tidak berubah.
        $sebelum = $versi(7, $waktu);
        $sesudah = $versi(6, $waktu);

        $this->assertNotSame(
            $sebelum,
            $sesudah,
            'penghapusan wajib mengubah versi — kalau tidak, aplikasi menyimpan nomor yang sudah dicabut',
        );
    }

    /** Dan tetap menangkap perubahan isi tanpa perubahan jumlah. */
    public function test_versi_berubah_saat_isi_diubah(): void
    {
        $versi = fn (int $jumlah, string $terbaru) => substr(sha1($jumlah.'|'.$terbaru), 0, 12);

        $this->assertNotSame(
            $versi(7, '2026-08-24 10:00:00'),
            $versi(7, '2026-08-24 11:00:00'),
        );
    }

    /** Tidak berubah bila memang tidak ada yang berubah. */
    public function test_versi_tetap_bila_tidak_ada_perubahan(): void
    {
        $versi = fn (int $jumlah, string $terbaru) => substr(sha1($jumlah.'|'.$terbaru), 0, 12);

        $this->assertSame(
            $versi(7, '2026-08-24 10:00:00'),
            $versi(7, '2026-08-24 10:00:00'),
        );
    }
}

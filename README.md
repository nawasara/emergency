# nawasara/emergency

Direktori nomor telepon darurat untuk framework superapp Nawasara. Nomor
dikelompokkan dan disajikan ke aplikasi warga, lengkap dengan penanda versi
supaya aplikasi dapat menyimpan salinannya untuk dipakai **tanpa jaringan**.

## Status v0.1.0

| Fitur | Status |
|---|---|
| Tabel nomor darurat + panel pengelola | ✅ siap |
| Pengelompokan (Darurat, Kesehatan, Layanan Publik, Bencana) | ✅ siap |
| Endpoint warga `GET /emergency/contacts` — terbuka, tanpa akun | ✅ siap |
| Penanda versi untuk cache luring | ✅ siap |
| Nonaktifkan tanpa menghapus | ✅ siap |
| Seeder nomor nasional (112, 110, 113, 118, 119, 117, 123) | ✅ siap |
| Nomor lokal Ponorogo | ⏳ diisi staf lewat panel |

## Kenapa layar ini harus bekerja tanpa jaringan

Nomor darurat dibutuhkan justru saat keadaan buruk — kecelakaan, banjir,
kebakaran — dan itulah saat sinyal paling mungkin hilang. Aplikasi karena itu
menyimpan salinan daftarnya, dan hanya mengunduh ulang bila penandanya
berubah.

Penanda itu (`meta.version`) dihitung dari **jumlah baris aktif DAN waktu ubah
terbaru**, bukan salah satunya:

- Waktu saja melewatkan **penghapusan** — mencabut sebuah nomor tidak mengubah
  `updated_at` baris mana pun yang tersisa, sehingga aplikasi akan terus
  menampilkan nomor yang sudah tidak berlaku. Nomor darurat yang salah lebih
  berbahaya daripada tidak ada nomor sama sekali.
- Jumlah saja melewatkan **perubahan isi** — mengganti satu nomor tidak
  mengubah jumlahnya.

Keduanya bersama menangkap tambah, ubah, dan hapus. Uji
`tests/DirectoryTest.php` mengunci kasus penghapusan secara khusus.

## Setup

```bash
composer require nawasara/emergency
php artisan migrate
php artisan db:seed --class="Nawasara\Emergency\Database\Seeders\PermissionSeeder"
php artisan db:seed --class="Nawasara\Emergency\Database\Seeders\ContactSeeder"
```

Tambahkan juga di `resources/css/app.css` — tanpa ini seluruh kelas Tailwind
di blade paket tidak ter-compile:

```css
@source "../../vendor/nawasara/emergency";
```

## Endpoint warga

```
GET /api/v1/emergency/contacts
```

**Terbuka tanpa akun**, sengaja. Orang yang baru memasang aplikasi dan belum
sempat mendaftar adalah justru orang yang mungkin membutuhkannya; menuntut
login sebelum menampilkan nomor pemadam kebakaran adalah kegagalan rancangan.
Isinya memang publik — nomor-nomor ini tercetak di brosur dan papan
pengumuman.

```jsonc
{
  "data": [
    {
      "group": "Darurat",
      "contacts": [
        {
          "name": "Panggilan Darurat Terpadu",
          "number": "112",
          "icon_name": "shield-alert",
          "description": "Satu nomor untuk semua keadaan darurat. Bebas pulsa.",
          "primary": true
        }
      ]
    }
  ],
  "meta": { "version": "a1b2c3d4e5f6" }
}
```

Kelompok diurutkan menurut **kemendesakan**, bukan abjad — "Bencana"
mendahului "Darurat" secara alfabet, dan itu urutan yang keliru untuk layar
yang dipindai orang dengan cepat. Kelompok di luar daftar bawaan tetap tampil,
di posisi paling akhir; staf boleh menambah kelompok baru tanpa menunggu
perubahan kode.

## Model

`EmergencyContact` — tabel `nawasara_emergency_contacts`

| Kolom | Catatan |
|---|---|
| `group` | Teks, bukan enum — daftar kelompok adalah keputusan tampilan yang boleh berubah tanpa migrasi |
| `name` | Nama yang dibaca warga |
| `number` | **String, bukan angka** — "112" dan "0352481113" kehilangan nol depannya sebagai integer |
| `icon_name` | Nama ikon Lucide tanpa awalan, mis. `shield-alert` |
| `primary` | Nomor utama kelompoknya, ditampilkan lebih menonjol |
| `is_active` | Nonaktif tanpa menghapus — nomor yang dicabut tetap punya jejak |
| `sort_order` | Urutan dalam kelompoknya |

## Permissions

| Permission | Untuk |
|---|---|
| `emergency.contact.view` | Melihat daftar di panel |
| `emergency.contact.manage` | Menambah, mengubah, menonaktifkan |

Sengaja dipisah: mengubah nomor darurat berarti mengubah ke mana warga
menelepon saat keadaan buruk, dan itu tidak memberi kesempatan kedua.

## Catatan seeder

`ContactSeeder` hanya berisi **nomor nasional** yang berlaku di mana pun.
Nomor lokal Ponorogo sengaja tidak ditebak — nomor darurat yang salah lebih
berbahaya daripada nomor yang belum ada. Nomor lokal diisi staf lewat panel,
dan panelnya ada justru supaya itu tidak menunggu rilis.

Seeder aman dijalankan berulang: ia memakai `updateOrCreate` pada nomor +
nama, sehingga tidak menimpa suntingan staf pada baris lain.

## Roadmap

- Nomor per kecamatan (puskesmas, polsek, posko) — menunggu master wilayah
- Riwayat perubahan nomor (siapa mengubah apa, kapan)
- Ekspor daftar untuk dicetak

## Author

Pringgo J. Saputro — Dinas Kominfo Kabupaten Ponorogo

## License

MIT

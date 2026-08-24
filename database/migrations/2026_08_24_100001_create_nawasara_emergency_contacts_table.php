<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nomor telepon darurat yang ditampilkan aplikasi warga.
 *
 * ⚠️ **Layar ini harus bekerja TANPA jaringan.** Nomor darurat dibutuhkan
 * justru saat keadaan buruk — kecelakaan, banjir, kebakaran — dan itulah saat
 * sinyal paling mungkin hilang. Aplikasi karena itu menyimpan salinannya, dan
 * hanya mengunduh ulang bila penandanya berubah.
 *
 * Karena itu tabel ini bukan sekadar daftar: ia harus dapat menjawab
 * "berubah atau tidak" dengan murah. Penandanya dihitung dari `updated_at`
 * terbaru dan jumlah baris — lihat `EmergencyDirectory::version()`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nawasara_emergency_contacts', function (Blueprint $table) {
            $table->id();

            /**
             * Kelompok tampilan, sudah ditetapkan mockup NomorPenting.jsx:
             * Darurat, Kesehatan, Layanan Publik, Bencana.
             *
             * Disimpan sebagai teks, bukan enum: daftar kelompok adalah
             * keputusan tampilan yang dapat berubah, dan mengubah enum
             * menuntut migrasi di produksi untuk sesuatu yang seharusnya
             * cukup diketik staf.
             */
            $table->string('group', 50);

            $table->string('name', 150);

            /**
             * Nomor sebagaimana ditelepon — termasuk nol di depan.
             *
             * String, BUKAN angka. "112" dan "0352481113" akan kehilangan nol
             * depannya sebagai integer, dan panggilan ke nomor yang salah
             * pada keadaan darurat adalah kegagalan yang tidak dapat
             * dimaafkan.
             */
            $table->string('number', 30);

            $table->string('icon_name', 50)->nullable();
            $table->text('description')->nullable();

            /**
             * Nomor utama kelompoknya — ditampilkan lebih menonjol.
             *
             * Bukan sekadar hiasan: pada keadaan darurat, warga memindai
             * layar dengan cepat, dan nomor yang paling sering dibutuhkan
             * harus paling mudah ditemukan.
             */
            $table->boolean('primary')->default(false);

            // Nonaktif tanpa menghapus — nomor yang sudah tidak berlaku tetap
            // punya jejak, dan menghapusnya membuat riwayat perubahan hilang.
            $table->boolean('is_active')->default(true);

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['is_active', 'group', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nawasara_emergency_contacts');
    }
};

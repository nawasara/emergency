<?php

namespace Nawasara\Emergency\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu nomor telepon darurat.
 *
 * Urutan kelompoknya ditetapkan di sini, bukan diserahkan pada abjad:
 * "Bencana" sebelum "Darurat" secara alfabet, padahal Darurat yang harus
 * lebih dulu dilihat.
 */
class EmergencyContact extends Model
{
    protected $table = 'nawasara_emergency_contacts';

    protected $fillable = [
        'group', 'name', 'number', 'icon_name',
        'description', 'primary', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'primary' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Urutan kelompok pada layar — dari yang paling mendesak.
     *
     * Mengikuti mockup `NomorPenting.jsx`. Kelompok yang tidak tercantum di
     * sini muncul paling akhir, bukan hilang: staf boleh menambah kelompok
     * baru tanpa menunggu perubahan kode.
     *
     * @var array<int,string>
     */
    public const GROUP_ORDER = [
        'Darurat',
        'Kesehatan',
        'Layanan Publik',
        'Bencana',
    ];

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    /** Posisi kelompok untuk pengurutan; kelompok tak dikenal jatuh ke akhir. */
    public static function groupRank(string $group): int
    {
        $index = array_search($group, self::GROUP_ORDER, true);

        return $index === false ? count(self::GROUP_ORDER) : $index;
    }
}

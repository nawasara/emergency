<?php

$prefix = 'nawasara-emergency';

/*
| Sidebar Nomor Darurat, bagian dari workspace Ponorogo Hub.
|
| ⚠️ Workspace id `ponorogo-hub` DIBAGI dengan nawasara/aspirations,
| nawasara/citizen, nawasara/news, nawasara/tourism, dan nawasara/pbb.
| Semuanya layanan untuk aplikasi warga, jadi muncul bersama alih-alih
| tersebar di antara menu infrastruktur.
|
| WorkspaceManager menggabungkan entri ber-id sama dan mengambil label + icon
| dari paket yang termuat LEBIH DULU secara alfabet, yaitu
| `nawasara-aspirations`. Label dan icon di sini HARUS sama persis dengan yang
| ada di sana, atau judul sidebar berubah tergantung paket mana yang terpasang.
|
| ⚠️ Submenu WAJIB diawali penanda `section` sendiri, atau entrinya menempel
| di bawah seksi milik paket lain dan terbaca sebagai bagian dari sana.
|
| Sebelumnya berkas ini memakai workspace sendiri dengan
| `group => 'Layanan Warga'`. Nilai itu TIDAK ada di
| WorkspaceManager::GROUP_ORDER, jadi menunya selama ini mendarat diam-diam di
| kelompok "Lainnya". Sekarang ikut 'Layanan' bersama saudaranya.
*/

return [
    [
        'workspace' => 'ponorogo-hub',
        'label' => 'Ponorogo Hub',
        'icon' => 'lucide-landmark',
        'group' => 'Layanan',
        'url' => '',
        'permission' => 'emergency.contact.view',
        'submenu' => [
            [
                'section' => 'Nomor Darurat',
                'icon' => 'lucide-phone-call',
                'permission' => 'emergency.contact.view',
            ],
            [
                'label' => 'Daftar Nomor',
                'icon' => 'lucide-phone',
                'url' => url($prefix.'/contacts'),
                'permission' => 'emergency.contact.view',
                'navigate' => true,
            ],
        ],
    ],
];

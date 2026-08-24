<?php

$prefix = 'nawasara-emergency';

return [
    [
        // Workspace sendiri — BUKAN menumpang milik paket lain.
        // WorkspaceManager mengambil label dan ikon dari paket yang load
        // pertama secara alfabet, jadi berbagi id membuat menu ini muncul
        // dengan nama paket lain.
        'workspace' => 'nawasara-emergency',
        'label' => 'Nomor Darurat',
        'icon' => 'lucide-phone-call',
        'group' => 'Layanan Warga',
        'url' => '',
        'permission' => 'emergency.contact.view',
        'submenu' => [
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

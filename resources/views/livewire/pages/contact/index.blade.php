<div>
    <x-slot name="breadcrumb">
        <livewire:nawasara-ui.shared-components.breadcrumb
            :items="[['label' => 'Layanan Warga', 'url' => '#'], ['label' => 'Nomor Darurat']]" />
    </x-slot>

    <x-nawasara-ui::page.container>
        <livewire:nawasara-emergency.contact.section.table />
    </x-nawasara-ui::page.container>
</div>

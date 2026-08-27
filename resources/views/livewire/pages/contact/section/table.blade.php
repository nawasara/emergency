<div>
    <x-nawasara-ui::page-header
        title="Nomor Darurat"
        description="Nomor yang tampil di aplikasi warga. Perubahan langsung terkirim — tidak menunggu rilis."
        :count="count($this->rows)">
        @can('emergency.contact.manage')
            <x-nawasara-ui::button color="primary" wire:click="create">
                Tambah Nomor
            </x-nawasara-ui::button>
        @endcan
    </x-nawasara-ui::page-header>

    {{-- Toolbar — pola yang sama dengan DNS Records (`nawasara-cloudflare`):
         saringan di kiri, pencarian mengisi ruang tengah, aksi menempel
         kanan. filter-panel meneleportasikan chip-nya ke bawah toolbar,
         jadi chip yang membungkus tidak menggeser barisnya. --}}
    <div class="space-y-2 mb-4">
        <div class="flex flex-col md:flex-row md:flex-nowrap md:items-center gap-2">
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <x-nawasara-ui::filter-panel
                    label="Filter"
                    :state="['groupFilter' => $groupFilter, 'statusFilter' => $statusFilter]"
                    :labels="[
                        'groupFilter' => $this->groupOptions,
                        'statusFilter' => ['active' => 'Aktif', 'inactive' => 'Nonaktif'],
                    ]">
                    <x-nawasara-ui::filter-group
                        label="Kelompok"
                        model="groupFilter"
                        :items="$this->groupOptions"
                        icon="lucide-folder" />

                    <x-nawasara-ui::filter-group
                        label="Status"
                        model="statusFilter"
                        :items="['active' => 'Aktif', 'inactive' => 'Nonaktif']"
                        icon="lucide-toggle-left" />
                </x-nawasara-ui::filter-panel>
            </div>

            {{-- prop `model`, bukan wire:model — lihat PHPDoc komponennya --}}
            <x-nawasara-ui::search-input
                model="search"
                placeholder="Cari nama, nomor, atau keterangan..." />
        </div>

        <div data-filter-chips class="flex flex-wrap items-center gap-2"></div>
    </div>

    <x-nawasara-ui::page.card>
        @if (count($this->rows) === 0)
            {{-- Dibedakan: "belum ada data" menuntut tindakan yang berbeda
                 dari "saringan tidak menghasilkan apa-apa". Satu pesan untuk
                 keduanya membuat staf mencari data yang sebenarnya ada. --}}
            @if ($search !== '' || $groupFilter !== '' || $statusFilter !== '')
                <x-nawasara-ui::empty-state
                    icon="lucide-search-x"
                    title="Tidak ada nomor yang cocok"
                    description="Ubah kata kunci atau saringannya." />
            @else
                <x-nawasara-ui::empty-state
                    icon="lucide-phone-off"
                    title="Belum ada nomor darurat"
                    description="Jalankan ContactSeeder untuk mengisi nomor nasional, lalu tambahkan nomor lokal Ponorogo." />
            @endif
        @else
            <x-nawasara-ui::table :headers="['Kelompok', 'Nama', 'Nomor', 'Utama', 'Status', '']" stickyLast>
                <x-slot:table>
                @foreach ($this->rows as $row)
                    <tr wire:key="kontak-{{ $row->id }}">
                        <td class="px-4 py-2.5 text-sm text-neutral-700 dark:text-neutral-200">
                            {{ $row->group }}
                        </td>
                        <td class="px-4 py-2.5">
                            <span class="text-sm text-neutral-800 dark:text-neutral-100">{{ $row->name }}</span>
                            @if ($row->description)
                                <p class="mt-0.5 text-xs text-neutral-500 dark:text-neutral-400">
                                    {{ $row->description }}
                                </p>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 font-mono text-sm text-neutral-800 dark:text-neutral-100">
                            {{ $row->number }}
                        </td>
                        <td class="px-4 py-2.5">
                            @if ($row->primary)
                                <x-nawasara-ui::badge color="warning">Utama</x-nawasara-ui::badge>
                            @endif
                        </td>
                        <td class="px-4 py-2.5">
                            <x-nawasara-ui::badge :color="$row->is_active ? 'success' : 'neutral'">
                                {{ $row->is_active ? 'Aktif' : 'Nonaktif' }}
                            </x-nawasara-ui::badge>
                        </td>
                        <td class="px-4 py-2.5 text-right">
                            {{-- Dropdown titik-tiga, pola yang sama dengan DNS
                                 Records. Tombol berjejer memakan lebar kolom
                                 dan tidak menyediakan tempat bagi aksi ketiga
                                 seperti Hapus. --}}
                            <x-nawasara-ui::dropdown-menu-action :id="$row->id" :items="[
                                [
                                    'type' => 'click',
                                    'label' => 'Ubah',
                                    'wire:click' => 'edit('.$row->id.')',
                                    'modal' => 'kontak-form',
                                    'icon' => 'lucide-pencil',
                                    'permission' => 'emergency.contact.manage',
                                ],
                                [
                                    'type' => 'click',
                                    'label' => $row->is_active ? 'Nonaktifkan' : 'Aktifkan',
                                    'wire:click' => 'toggle('.$row->id.')',
                                    'icon' => $row->is_active ? 'lucide-eye-off' : 'lucide-eye',
                                    'permission' => 'emergency.contact.manage',
                                ],
                                [
                                    'type' => 'click',
                                    'label' => 'Hapus',
                                    'wire:click' => 'delete('.$row->id.')',
                                    'icon' => 'lucide-trash-2',
                                    'confirm' => 'Hapus nomor "'.$row->name.'" secara permanen? Untuk menonaktifkan sementara, pakai Nonaktifkan.',
                                    'permission' => 'emergency.contact.manage',
                                ],
                            ]" />
                        </td>
                    </tr>
                @endforeach
                </x-slot:table>
            </x-nawasara-ui::table>
        @endif
    </x-nawasara-ui::page.card>

    <x-nawasara-ui::modal id="kontak-form" :title="$editingId ? 'Ubah Nomor' : 'Tambah Nomor'">
        <div class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-nawasara-ui::form.select
                        label="Kelompok"
                        wire:model="group"
                        :options="$this->groupOptions"
                        placeholder="Pilih kelompok"
                        hint="Boleh kelompok baru — akan muncul paling akhir di aplikasi." />
                </div>
                <div>
                    <x-nawasara-ui::form.input
                        type="number"
                        label="Urutan"
                        wire:model="sort_order" />
                </div>
            </div>

            <div>
                <x-nawasara-ui::form.input
                    type="text"
                    label="Nama"
                    wire:model="name"
                    placeholder="Panggilan Darurat Terpadu" />
            </div>

            <div>
                <x-nawasara-ui::form.input
                    type="text"
                    label="Nomor"
                    wire:model="number"
                    placeholder="112" />
                {{-- form.input tidak punya prop hint — hanya select & textarea --}}
                <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                    Tulis persis seperti yang ditelepon, termasuk nol di depan.
                </p>
            </div>

            <div>
                <x-nawasara-ui::form.input
                    type="text"
                    label="Nama Ikon"
                    wire:model="icon_name"
                    placeholder="shield-alert" />
                <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                    Nama ikon Lucide tanpa awalan, mis. <code>shield-alert</code>.
                </p>
            </div>

            <div>
                <x-nawasara-ui::form.textarea
                    label="Keterangan"
                    wire:model="description"
                    :rows="2"
                    hint="Opsional — mis. 'Bebas pulsa'." />
            </div>

            <div>
                <x-nawasara-ui::form.checkbox
                    wire:model="primary"
                    label="Tandai sebagai nomor utama kelompok ini" />
            </div>
        </div>

        <x-slot name="footer">
            <x-nawasara-ui::button
                color="neutral"
                x-on:click="$dispatch('close-modal', 'kontak-form')">
                Batal
            </x-nawasara-ui::button>

            {{-- wire:click, bukan submit: modal merender footer DI LUAR div
                 konten, sehingga tombol submit lolos dari form-nya. --}}
            <x-nawasara-ui::button color="primary" wire:click="save">
                Simpan
            </x-nawasara-ui::button>
        </x-slot>
    </x-nawasara-ui::modal>
</div>

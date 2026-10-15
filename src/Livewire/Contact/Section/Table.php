<?php

namespace Nawasara\Emergency\Livewire\Contact\Section;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Nawasara\AuthPrimitives\Attributes\RequiresSudo;
use Nawasara\AuthPrimitives\Traits\WithSudo;
use Nawasara\Emergency\Models\EmergencyContact;

/**
 * Kelola nomor darurat: daftar, tambah, ubah, aktif/nonaktif.
 *
 * Panelnya ada supaya menambah nomor lokal TIDAK menunggu rilis. Nomor
 * darurat berubah — puskesmas pindah, posko bencana dibuka — dan menunggu
 * penerbitan paket untuk itu berarti warga memegang nomor yang salah selama
 * berhari-hari.
 */
class Table extends Component
{
    use WithSudo;

    /**
     * Saringan toolbar — di URL supaya tautan hasil saringan dapat dibagikan
     * dan tetap bertahan setelah muat ulang.
     */
    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $groupFilter = '';

    #[Url(except: '')]
    public string $statusFilter = '';

    // Audit keamanan 15 Oktober 2026: hanya diisi server (edit), dikunci
    // supaya payload wire tidak dapat mengalihkan simpan ke nomor lain.
    #[Locked]
    public ?int $editingId = null;

    public string $group = '';
    public string $name = '';
    public string $number = '';
    public ?string $icon_name = null;
    public ?string $description = null;
    public bool $primary = false;
    public int $sort_order = 0;

    #[Computed]
    public function rows()
    {
        return EmergencyContact::query()
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(fn ($w) => $w
                    ->where('name', 'like', $term)
                    ->orWhere('number', 'like', $term)
                    ->orWhere('description', 'like', $term));
            })
            ->when($this->groupFilter !== '', fn ($q) => $q->where('group', $this->groupFilter))
            ->when($this->statusFilter !== '', fn ($q) => $q->where('is_active', $this->statusFilter === 'active'))
            ->get()
            // Diurutkan menurut kemendesakan kelompoknya, bukan abjad —
            // "Bencana" mendahului "Darurat" secara alfabet, padahal Darurat
            // yang harus lebih dulu terlihat.
            ->sortBy([
                fn ($a, $b) => EmergencyContact::groupRank($a->group)
                    <=> EmergencyContact::groupRank($b->group),
                fn ($a, $b) => $a->sort_order <=> $b->sort_order,
                fn ($a, $b) => strcmp($a->name, $b->name),
            ])
            ->values();
    }

    #[Computed]
    public function groupOptions(): array
    {
        $configured = (array) config('nawasara-emergency.groups', []);

        // Kelompok yang sudah dipakai ikut ditawarkan meski tidak ada di
        // config — staf boleh membuat kelompok baru, dan menyembunyikannya
        // dari pilihan akan membuat mereka mengetik ulang tiap kali.
        $used = EmergencyContact::distinct()->pluck('group')->all();

        $all = array_values(array_unique(array_merge($configured, $used)));

        return array_combine($all, $all);
    }

    public function create(): void
    {
        $this->authorize('emergency.contact.manage');

        $this->reset(['editingId', 'group', 'name', 'number', 'icon_name', 'description', 'primary', 'sort_order']);
        $this->resetErrorBag();

        $this->dispatch('modal-open:kontak-form');
    }

    public function edit(int $id): void
    {
        $this->authorize('emergency.contact.manage');

        $contact = EmergencyContact::findOrFail($id);

        $this->editingId = $contact->id;
        $this->group = $contact->group;
        $this->name = $contact->name;
        $this->number = $contact->number;
        $this->icon_name = $contact->icon_name;
        $this->description = $contact->description;
        $this->primary = $contact->primary;
        $this->sort_order = $contact->sort_order;

        $this->resetErrorBag();
        $this->dispatch('modal-open:kontak-form');
    }

    public function save(): void
    {
        $this->authorize('emergency.contact.manage');

        $data = $this->validate([
            'group' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:150'],

            // Angka, spasi, tanda hubung, kurung, dan plus — bentuk yang
            // benar-benar ditulis orang. TIDAK divalidasi sebagai integer:
            // "112" dan "0352481113" akan kehilangan nol depannya, dan
            // menelepon nomor yang salah saat darurat tidak dapat dimaafkan.
            'number' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\-\s]+$/'],

            'icon_name' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'primary' => ['boolean'],
            'sort_order' => ['integer', 'min:0', 'max:9999'],
        ], [
            'number.regex' => 'Nomor hanya boleh berisi angka, spasi, dan tanda + ( ) -.',
        ]);

        EmergencyContact::updateOrCreate(
            ['id' => $this->editingId],
            $data + ['is_active' => true],
        );

        $this->dispatch('modal-close:kontak-form');
        $this->dispatch('toast', type: 'success', message: 'Nomor tersimpan.');

        unset($this->rows, $this->groupOptions);
    }

    /**
     * Nonaktifkan, BUKAN hapus.
     *
     * Nomor yang dicabut tetap punya jejak, dan menghapusnya membuat riwayat
     * perubahan hilang — padahal justru itu yang ditanyakan ketika seseorang
     * mengeluh menelepon nomor yang tidak aktif.
     */
    public function toggle(int $id): void
    {
        $this->authorize('emergency.contact.manage');

        $contact = EmergencyContact::findOrFail($id);
        $contact->update(['is_active' => ! $contact->is_active]);

        unset($this->rows);
    }

    /**
     * Hapus permanen.
     *
     * Berbeda dari [toggle] yang hanya menonaktifkan. Keduanya disediakan
     * karena keduanya dibutuhkan untuk hal yang berbeda:
     *
     *   nonaktifkan  nomor yang sementara tidak berlaku — posko yang tutup,
     *                puskesmas yang sedang pindah. Jejaknya tetap ada, dan
     *                dapat dihidupkan lagi.
     *   hapus        baris yang memang keliru — salah ketik, ganda, atau
     *                nomor yang tidak pernah benar. Menyimpannya sebagai
     *                "nonaktif" hanya membuat daftar penuh sampah yang harus
     *                dibaca ulang setiap kali seseorang menyunting.
     *
     * Digerbang `emergency.contact.manage` seperti aksi lain, dan blade
     * memasang konfirmasi — nomor darurat yang terhapus tanpa sengaja tidak
     * terlihat sampai ada yang membutuhkannya.
     */
    // Audit keamanan 15 Oktober 2026 (S6): hapus permanen wajib sudo
    // (AGENTS.md §6). Nonaktifkan lewat toggle() tetap tanpa sudo.
    #[RequiresSudo(reason: 'menghapus nomor darurat')]
    public function delete(int $id): void
    {
        $this->authorize('emergency.contact.manage');

        EmergencyContact::findOrFail($id)->delete();

        $this->dispatch('toast', type: 'success', message: 'Nomor dihapus.');

        unset($this->rows, $this->groupOptions);
    }

    public function render()
    {
        return view('nawasara-emergency::livewire.pages.contact.section.table');
    }
}

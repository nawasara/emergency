<?php

namespace Nawasara\Emergency\Livewire\Contact;

use Livewire\Component;

/**
 * Halaman Nomor Darurat — cangkang saja; isinya di Section/Table.
 */
class Index extends Component
{
    public function render()
    {
        return view('nawasara-emergency::livewire.pages.contact.index')
            ->layout('nawasara-ui::components.layouts.app');
    }
}

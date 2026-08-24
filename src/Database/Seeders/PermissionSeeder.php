<?php

namespace Nawasara\Emergency\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'emergency.contact.view',

            // Terpisah dari view: mengubah nomor darurat berarti mengubah ke
            // mana warga menelepon saat keadaan buruk. Nomor yang salah tidak
            // memberi kesempatan kedua.
            'emergency.contact.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $role = Role::where('name', 'developer')->first();

        if ($role) {
            $role->givePermissionTo($permissions);
        }
    }
}

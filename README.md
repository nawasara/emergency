# nawasara/emergency

Emergency phone number directory for the Nawasara superapp framework. Numbers are grouped and served to the citizen app, along with a version marker so the app can keep a copy for use without a network connection.

## Status v0.1.0

| Feature | Status |
|---|---|
| Emergency number table + admin panel | ready |
| Grouping (Darurat, Kesehatan, Layanan Publik, Bencana) | ready |
| Citizen endpoint `GET /emergency/contacts`, open, no account needed | ready |
| Version marker for offline cache | ready |
| Deactivate without deleting | ready |
| National number seeder (112, 110, 113, 118, 119, 117, 123) | ready |
| Ponorogo local numbers | not built yet, staff fill these in via the panel |

## Why this screen has to work without a network

Emergency numbers are needed exactly when things go wrong: accidents, floods, fires, which is also when signal is most likely to drop. So the app keeps a copy of the list and only re-downloads it when the marker changes.

That marker (`meta.version`) is computed from both the count of active rows and the most recent update time, not one of them alone:

- Time alone misses deletions. Removing a number does not change the `updated_at` of any remaining row, so the app would keep showing a number that no longer applies. A wrong emergency number is more dangerous than no number at all.
- Count alone misses content changes. Replacing one number does not change the count.

Together they catch adds, edits, and deletes. `tests/DirectoryTest.php` specifically locks down the deletion case.

## Setup

```bash
composer require nawasara/emergency
php artisan migrate
php artisan db:seed --class="Nawasara\Emergency\Database\Seeders\PermissionSeeder"
php artisan db:seed --class="Nawasara\Emergency\Database\Seeders\ContactSeeder"
```

Also add this to `resources/css/app.css`. Without it, none of the Tailwind classes in the package blades compile:

```css
@source "../../vendor/nawasara/emergency";
```

## Citizen endpoint

```
GET /api/v1/emergency/contacts
```

Open, no account required, on purpose. Someone who just installed the app and has not registered yet is exactly the person who might need it; requiring login before showing the fire department number would be a design failure. The content is public anyway: these numbers are printed on brochures and notice boards.

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

Groups are ordered by urgency, not alphabetically. "Bencana" would come before "Darurat" alphabetically, and that is the wrong order for a screen people scan quickly. Groups outside the default list still show up, placed last; staff can add new groups without waiting for a code change.

## Model

`EmergencyContact`, table `nawasara_emergency_contacts`

| Column | Notes |
|---|---|
| `group` | Text, not an enum. The list of groups is a display decision that can change without a migration |
| `name` | The name citizens read |
| `number` | String, not a number. "112" and "0352481113" lose their leading zero as an integer |
| `icon_name` | Lucide icon name without the prefix, e.g. `shield-alert` |
| `primary` | The main number of its group, shown more prominently |
| `is_active` | Deactivate without deleting; a withdrawn number keeps its trail |
| `sort_order` | Order within its group |

## Permissions

| Permission | For |
|---|---|
| `emergency.contact.view` | Viewing the list in the panel |
| `emergency.contact.manage` | Adding, editing, deactivating |

These are kept separate on purpose: changing an emergency number changes where citizens call when things go wrong, and that leaves no room for a second chance.

## Seeder notes

`ContactSeeder` only contains national numbers that apply everywhere. Ponorogo local numbers are deliberately not guessed. A wrong emergency number is more dangerous than one that is not there yet. Local numbers are filled in by staff via the panel, and the panel exists precisely so that this does not have to wait for a release.

The seeder is safe to run repeatedly: it uses `updateOrCreate` on number + name, so it does not overwrite staff edits on other rows.

## Roadmap

- Numbers per sub-district (puskesmas, polsek, posko), waiting on the regional master data
- Change history for numbers (who changed what, when)
- Export the list for printing

## Author

Pringgo J. Saputro, Dinas Kominfo Kabupaten Ponorogo

## License

MIT

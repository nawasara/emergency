<?php

namespace Nawasara\Emergency;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Symfony\Component\Finder\Finder;

class EmergencyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nawasara-emergency');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Dijaga is_dir(): `view:cache` jatuh bila jalurnya tidak ada, dan
        // paket ini belum tentu punya folder components.
        $components = __DIR__.'/../resources/views/components';

        if (is_dir($components)) {
            Blade::anonymousComponentPath($components, 'nawasara-emergency');
        }

        $this->registerCitizenRoutes();
        $this->registerLivewire();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/nawasara-emergency.php', 'nawasara-emergency');
    }

    /**
     * Endpoint aplikasi warga — TERBUKA, tanpa `api.citizen`.
     *
     * Lihat catatan pada routes/citizen.php: nomor darurat harus dapat
     * dijangkau siapa pun, dan isinya memang publik.
     *
     * Pembatasan per-IP dengan batas longgar — menahan pengambilan massal,
     * bukan pemakaian wajar. Jawabannya kecil dan aplikasi menyimpannya, jadi
     * pemakaian normal hanya beberapa panggilan per pemasangan.
     */
    protected function registerCitizenRoutes(): void
    {
        $prefix = (string) config('nawasara-api.route.prefix', 'api/v1').'/emergency';

        Route::prefix($prefix)
            ->middleware(['api', 'throttle:120,1'])
            ->name('nawasara-emergency.citizen.')
            ->group(__DIR__.'/../routes/citizen.php');
    }

    /**
     * Komponen Livewire ditemukan sendiri lewat Finder — pola yang sama
     * dipakai paket lain, supaya menambah halaman tidak menuntut mendaftar
     * satu per satu di sini.
     */
    protected function registerLivewire(): void
    {
        $namespace = 'Nawasara\Emergency\Livewire';
        $basePath = __DIR__.'/Livewire';

        if (! is_dir($basePath)) {
            return;
        }

        $finder = new Finder();
        $finder->files()->in($basePath)->name('*.php');

        foreach ($finder as $file) {
            $relativePath = str_replace('/', '\\', $file->getRelativePathname());
            $class = $namespace.'\\'.Str::beforeLast($relativePath, '.php');

            if (class_exists($class)) {
                $alias = 'nawasara-emergency.'.
                    Str::of($relativePath)
                        ->replace('.php', '')
                        ->replace('\\', '.')
                        ->replace('/', '.')
                        ->explode('.')
                        ->map(fn ($segment) => Str::kebab($segment))
                        ->join('.');

                Livewire::component($alias, $class);
            }
        }
    }
}

<?php
namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Http\Livewire\Auth\Login;
use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Filament::serving(function () {
            Filament::registerViteTheme('resources/css/filament.css');
        });

        // Custom Login
        Login::setFormFields([
            'email' => 'email',
            'password' => 'password',
            'remember' => true, // Menambahkan Remember Me
        ]);
    }
}

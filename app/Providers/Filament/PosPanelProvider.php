<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PosPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('pos')
            ->path('pos')
            ->login()
            ->colors([
                'primary' => Color::Teal,
                'gray'    => Color::Zinc,
                'success' => Color::Green,
                'warning' => Color::Orange,
                'danger'  => Color::Red,
            ])
            ->darkMode(false) // POS siempre en modo claro para legibilidad en caja
            ->brandName('BodegaPOS — Caja')
            ->favicon(asset('favicon.ico'))

            // ── El POS tiene una sola página custom ───────────────────────────
            ->discoverPages(
                in: app_path('Filament/Pos/Pages'),
                for: 'App\\Filament\\Pos\\Pages'
            )
            ->pages([
                \App\Filament\Pos\Pages\PosTerminalPage::class,
            ])

            // Sin widgets en el POS
            ->widgets([])

            // Sin navegación lateral — pantalla completa
            ->sidebarCollapsibleOnDesktop()

            // Notificaciones de base de datos habilitadas para el cajero
            ->databaseNotifications()

            // ── Selector de Paneles en Topbar ─────────────────────────────────
            ->renderHook(
                \Filament\View\PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): string => \Illuminate\Support\Facades\Blade::render('
                    @if(auth()->user()->can("switch_panels"))
                        <div style="display: flex; align-items: center; margin-left: auto; margin-right: 1rem;">
                            <x-filament::button href="{{ url(\'/admin\') }}" tag="a" color="info" icon="heroicon-o-building-storefront" size="sm">
                                ERP Administrativo
                            </x-filament::button>
                        </div>
                    @endif
                ')
            )

            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class]);
    }
}

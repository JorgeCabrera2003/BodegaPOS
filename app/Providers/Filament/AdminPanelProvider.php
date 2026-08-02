<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Widgets\LowStockWidget;
use App\Filament\Admin\Widgets\SalesChartWidget;
use App\Filament\Admin\Widgets\StatsOverviewWidget;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Pxlrbt\FilamentActivityLog\FilamentActivityLogPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary'   => Color::Indigo,
                'gray'      => Color::Slate,
                'success'   => Color::Emerald,
                'warning'   => Color::Amber,
                'danger'    => Color::Rose,
                'info'      => Color::Sky,
            ])
            ->darkMode(true)
            ->brandName('BodegaPOS — Admin')
            ->brandLogo(null)
            ->favicon(asset('favicon.ico'))

            // ── Sidebar colapsable en Desktop ─────────────────────────────────
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('16rem')

            // ── Recursos ──────────────────────────────────────────────────────
            ->discoverResources(
                in: app_path('Filament/Admin/Resources'),
                for: 'App\\Filament\\Admin\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Admin/Pages'),
                for: 'App\\Filament\\Admin\\Pages'
            )
            ->discoverWidgets(
                in: app_path('Filament/Admin/Widgets'),
                for: 'App\\Filament\\Admin\\Widgets'
            )
            ->pages([
                \App\Filament\Admin\Pages\Dashboard::class,
            ])
            ->widgets([])

            // ── Navegación clasificada ────────────────────────────────────────
            ->navigationGroups([
                NavigationGroup::make('Ventas')
                    ->icon('heroicon-o-receipt-percent')
                    ->collapsible(),
                NavigationGroup::make('Inventario')
                    ->icon('heroicon-o-archive-box')
                    ->collapsible(),
                NavigationGroup::make('Finanzas')
                    ->icon('heroicon-o-banknotes')
                    ->collapsible(),
                NavigationGroup::make('Sistema')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsible()
                    ->collapsed(),
            ])

            // ── Notificaciones ───────────────────────────────────────────────
            ->databaseNotifications()

            // ── Selector de Paneles en Topbar ─────────────────────────────────
            ->renderHook(
                \Filament\View\PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): string => \Illuminate\Support\Facades\Blade::render('
                    @if(auth()->user()->can("switch_panels"))
                        <div style="display: flex; align-items: center; margin-left: auto; margin-right: 1rem;">
                            <x-filament::button href="{{ url(\'/pos\') }}" tag="a" color="primary" icon="heroicon-o-shopping-bag" size="sm">
                                Terminal POS
                            </x-filament::button>
                        </div>
                    @endif
                ')
            )

            // ── Plugins ───────────────────────────────────────────────────────
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make()
                    ->navigationGroup('Sistema'),
                // FilamentActivityLogPlugin::make(),
            ])

            // ── Middleware ────────────────────────────────────────────────────
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

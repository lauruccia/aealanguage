<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Css;

// IMPORTA LA TUA DASHBOARD CUSTOM (la creiamo nello step successivo)
use App\Filament\Pages\AdminDashboard;

// IMPORTA I TUOI WIDGET (li creiamo noi)
use App\Filament\Widgets\AdminStatsOverview;
use App\Filament\Widgets\TodayLessonsTable;
// use App\Filament\Widgets\AdminAlerts; // lo aggiungiamo dopo

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')          // ✅ id "parlante"
            ->path('admin')
            ->login()

            ->colors([
                'primary' => Color::Amber,
            ])
            //->viteTheme('resources/css/filament/admin/theme.css')

->brandLogo(asset('images/logo-scuola.png'))
->brandLogoHeight('2.25rem')
->brandName('A&A Language Center') // opzionale: niente testo

            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\\Filament\\Resources'
            )

            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\\Filament\\Pages'
            )

            // ✅ qui scegliamo la dashboard custom (al posto di Pages\Dashboard)
->pages([
    AdminDashboard::class,
])

            ->discoverWidgets(
                in: app_path('Filament/Widgets'),
                for: 'App\\Filament\\Widgets'
            )

            // ✅ widget "globali" / sempre disponibili
            ->widgets([
                Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class, // opzionale: puoi toglierlo
            ])

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

            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    public function boot(): void
{
    FilamentAsset::register([
        Css::make('filament-admin-theme', asset('css/filament-admin.css')),
    ]);
}


}

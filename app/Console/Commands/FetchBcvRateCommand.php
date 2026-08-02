<?php

namespace App\Console\Commands;

use App\Models\ExchangeRate;
use App\Models\User;
use App\Notifications\LowStockNotification;
use App\Services\CurrencyService;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchBcvRateCommand extends Command
{
    /**
     * Signature del comando Artisan.
     */
    protected $signature = 'currency:fetch-bcv
                            {--force : Forzar fetch aunque ya se haya ejecutado hoy}';

    protected $description = 'Sincroniza la tasa oficial BCV desde la API ve.dolarapi.com';

    public function __construct(
        protected CurrencyService $currency
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('🔄 Consultando tasa BCV desde ve.dolarapi.com...');

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Accept' => 'application/json', 
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                ])
                ->get(config('app.bcv_api_url', 'https://ve.dolarapi.com/v1/dolares/oficial'));

            if (! $response->successful()) {
                $this->logFailure("API respondió con código {$response->status()}");
                return self::FAILURE;
            }

            $data = $response->json();

            // La API retorna: {"promedio": 36.50, "fuente": "BCV", ...}
            $rate = (float) ($data['promedio'] ?? $data['precio'] ?? 0);

            if ($rate <= 0) {
                $this->logFailure('La tasa recibida es inválida: ' . json_encode($data));
                return self::FAILURE;
            }

            // Verificar si ya existe una tasa igual para evitar duplicados
            $lastRate = ExchangeRate::latestRate()->first();
            if ($lastRate && abs($lastRate->rate - $rate) < 0.001 && ! $this->option('force')) {
                $this->info("ℹ️  La tasa no cambió ({$rate}). No se insertó duplicado.");
                $this->currency->updateCachedRate($rate);
                return self::SUCCESS;
            }

            // Insertar en tabla inmutable (append-only, NO update)
            $exchangeRate = ExchangeRate::create([
                'currency_code' => 'USD',
                'rate'          => $rate,
                'source'        => 'BCV',
                'notes'         => 'Sync automático — ' . now('America/Caracas')->format('d/m/Y H:i'),
            ]);

            // Actualizar cache Redis
            $this->currency->updateCachedRate($rate);

            $this->info("✅ Tasa BCV actualizada: 1 USD = Bs. {$rate}");
            $this->line("   ID registrado: #{$exchangeRate->id}");

            // Notificar a admins del cambio de tasa
            User::role(['super_admin', 'admin'])->get()->each(function (User $admin) use ($rate) {
                FilamentNotification::make()
                    ->success()
                    ->title('Tasa BCV Actualizada')
                    ->body("Nueva tasa: 1 USD = Bs. {$rate}")
                    ->sendToDatabase($admin);
            });

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->logFailure($e->getMessage());
            return self::FAILURE;
        }
    }

    private function logFailure(string $message): void
    {
        Log::error("FetchBcvRateCommand: {$message}");
        $this->error("❌ Error al sincronizar tasa BCV: {$message}");

        // Notificar a super_admin del fallo
        User::role('super_admin')->get()->each(function (User $admin) use ($message) {
            FilamentNotification::make()
                ->danger()
                ->title('Error: Sync Tasa BCV Fallido')
                ->body("No se pudo sincronizar la tasa BCV. Error: {$message}")
                ->sendToDatabase($admin);
        });
    }
}

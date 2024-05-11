<?php

namespace App\Console\Commands;

use App\Jobs\CollectWeatherData;
use App\Models\Location;
use App\Services\Weather\WeatherServicesFactory;
use Illuminate\Console\Command;

class WeatherAggregate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weather:aggregate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make jobs for aggregation weather.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $locations = Location::all();
        $weatherProviders = WeatherServicesFactory::getAvailableServices();

        foreach ($locations as $location) {
            foreach ($weatherProviders as $type => $serviceName) {
                CollectWeatherData::dispatch([
                    'locationId' => $location->id,
                    'serviceType' => $type,
                ]);
            }
        }
    }
}

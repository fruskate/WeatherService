<?php

namespace App\Jobs;

use App\Models\Location;
use App\Services\Weather\WeatherServicesFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CollectWeatherData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $location = Location::find($this->data['locationId']);


        if (!$location) {
            \Log::channel('error')->error("Not found Location with id: {$this->data['locationId']}!");
            return;
        }

        try {
            $weatherService = WeatherServicesFactory::create($this->data['serviceType']);
            $weatherData = $weatherService->fetchWeatherData($location->lat, $location->lon);
            $weatherData->location_id = $location->id;
            $weatherData->save();
        } catch (\Exception $e) {
            \Log::channel('error')->error("Error collecting weather data for location {$location->id} from provider {$this->data['serviceType']}: " . $e->getMessage());
        }
    }
}

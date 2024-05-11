<?php

namespace App\Services\Weather;

use App\Services\Weather\Adapters\OpenWeatherMapService;
use App\Services\Weather\Adapters\WeatherApiService;

class WeatherServicesFactory
{
    public static function create($type) {
        switch ($type) {
            case 1:
                return new OpenWeatherMapService();
            case 2:
                return new WeatherApiService();
            default:
                throw new \Exception("Unsupported weather service type");
        }
    }

    public static function getAvailableServices(): array
    {
        return [
            1 => 'OpenWeatherMapService',
            2 => 'WeatherApiService'
        ];
    }
}

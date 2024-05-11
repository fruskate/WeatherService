<?php

namespace App\Services\Weather;

use App\Models\WeatherData;

interface WeatherServiceInterface
{
    public function fetchWeatherData($latitude, $longitude): WeatherData;
}

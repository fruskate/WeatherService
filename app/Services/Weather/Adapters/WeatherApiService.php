<?php

namespace App\Services\Weather\Adapters;

use App\Models\WeatherData;
use App\Services\Weather\WeatherServiceInterface;
use Carbon\Carbon;
use GuzzleHttp\Client;

class WeatherApiService implements WeatherServiceInterface
{

    public function fetchWeatherData($latitude, $longitude): WeatherData
    {
        $response = $this->makeApiRequest($latitude, $longitude);

        if ($response === null) {
            throw new \Exception("Failed to retrieve data from weather API.");
        }

        return $this->transformResponseToWeatherData($response);
    }

    private function makeApiRequest($latitude, $longitude)
    {
        $baseUrl = 'http://api.weatherapi.com/v1/current.json';
        $apiKey = 'e9dd73fdc3cd490791362926240805';

        $fullUrl = "{$baseUrl}?q={$latitude},{$longitude}&key={$apiKey}";

        $client = new Client();

        try {
            $response = $client->request('GET', $fullUrl);
            $data = json_decode($response->getBody()->getContents(), true);
            return $data;
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            \Log::channel('error')->error("API request failed: " . $e->getMessage());
            return null;
        }
    }

    private function transformResponseToWeatherData($response)
    {
        $weatherData = new WeatherData();

        $weatherData->temp = $response['current']['temp_f'];
        $weatherData->feels_like = $response['current']['feelslike_f'];
        $weatherData->pressure = $response['current']['pressure_mb'];
        $weatherData->humidity = $response['current']['humidity'];
        $weatherData->collected_at = Carbon::createFromTimestamp($response['current']['last_updated_epoch'], 'UTC');

        return $weatherData;
    }
}

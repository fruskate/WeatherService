<?php

namespace App\Services\Weather\Adapters;

use App\Models\WeatherData;
use Carbon\Carbon;
use GuzzleHttp\Client;

class OpenWeatherMapService
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
        $baseUrl = 'https://api.openweathermap.org/data/2.5/weather';
        $apiKey = '210a6469c633f37a2d9a6738a59ab633';

        $fullUrl = "{$baseUrl}?lat={$latitude}&lon={$longitude}&appid={$apiKey}";

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

    private function transformResponseToWeatherData($response): WeatherData
    {
        $weatherData = new WeatherData();

        $weatherData->temp = $response['main']['temp'];
        $weatherData->feels_like = $response['main']['feels_like'];
        $weatherData->pressure = $response['main']['pressure'];
        $weatherData->humidity = $response['main']['humidity'];
        $weatherData->collected_at = Carbon::createFromTimestamp($response['dt'], 'UTC');

        return $weatherData;
    }
}

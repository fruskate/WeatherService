<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\WeatherData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LocationController extends Controller
{
    public function addLocation(Request $request)
    {
        try {
            $validated = $request->validate([
                'lat' => 'required|numeric|between:-90,90',
                'lon' => 'required|numeric|between:-180,180',
            ]);

            $location = Location::firstOrCreate($validated);

        } catch (\Exception $e) {
            \Log::channel('error')->error("Validation error." . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'response' => [
                    'description' => $e->getMessage(),
                ]
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'response' => [
                'location_id' => $location->id,
                'description' => 'Successfully created location.',
            ]
        ]);
    }

    public function getAverageWeather(Request $request, $locationId)
    {
        list($from, $to) = $this->getDatesFromRequest($request);
        $averageWeather = $this->getAverageWeatherForLocation($locationId, $from, $to);

        return response()->json([
            'status' => 'ok',
            'response' => [
                'description' => "Average data collected from {$from} to {$to}",
                'data' => $averageWeather
            ]
        ]);
    }

    private function getDatesFromRequest(Request $request)
    {
        return [
            $request->input('start_date', Carbon::now()->subMonth()->format('Y-m-d')),
            $request->input('end_date', Carbon::now()->format('Y-m-d')),
        ];
    }

    private function getAverageWeatherForLocation($locationId, $startDate, $endDate)
    {
        $cacheKey = "location_{$locationId}_weather_from_{$startDate}_to_{$endDate}";
        return Cache::remember($cacheKey, 60, function () use ($locationId, $startDate, $endDate) {
            return WeatherData::where('location_id', $locationId)
                ->whereDate('collected_at', '>=', Carbon::parse($startDate)->startOfDay()->toDateTimeString())
                ->whereDate('collected_at', '<=', Carbon::parse($endDate)->endOfDay()->toDateTimeString())
                ->selectRaw('AVG(temp) as average_temp,
                             AVG(humidity) as average_humidity,
                             AVG(pressure) as average_pressure')
                ->first();
        });
    }
}

<?php

class WeatherService
{
    public static function fetchWeather(string $location)
    {
        $config = require __DIR__ . '/../config/app.php';
        $apiKey = $config['openweather']['api_key'];
        $url = sprintf('https://api.openweathermap.org/data/2.5/weather?q=%s&units=metric&lang=pt_br&appid=%s', rawurlencode($location), $apiKey);

        $response = @file_get_contents($url);
        if ($response === false) {
            return null;
        }

        $data = json_decode($response, true);
        if (!isset($data['weather'][0])) {
            return null;
        }

        return [
            'temperature' => $data['main']['temp'] ?? null,
            'condition' => $data['weather'][0]['description'] ?? null,
            'icon' => isset($data['weather'][0]['icon']) ? 'https://openweathermap.org/img/wn/' . $data['weather'][0]['icon'] . '@2x.png' : null
        ];
    }
}

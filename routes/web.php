<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

// Dokumentation für die Design-Aufgabe:
// In dieser Route wird die Startseite des NASA APOD Explorers geladen.
// Hier wurde kein zusätzlicher Code für die Design-Aufgabe implementiert,
// da diese hauptsächlich in der Blade-Vorlage und im JavaScript-Code umgesetzt wurde.

Route::get('/', function () {
    $apiKey = 'zZX3lCSmeyb0JN45fcMazf6Oq6mja6WXRwbwaQ9W';
    $response = Http::withoutVerifying()->get('https://api.nasa.gov/planetary/apod', [
        'api_key' => $apiKey,
    ]);

    if ($response->successful()) {
        $data = $response->json();
        return view('welcome', ['apod' => $data]);
    }

    return view('welcome', ['error' => 'Fehler beim Abrufen der APOD-Daten: ' . $response->status()]);
});

// Dokumentation für die zusätzliche Aufgabe:
// In dieser Route wird die API-Anfrage für mehrere APOD-Einträge implementiert.
// Hier wurde ein Caching-Mechanismus hinzugefügt, um die Anzahl der Anfragen an die NASA API zu reduzieren
// und die Ladezeit der Webseite zu verbessern.

Route::get('/api/apod', function () {
    $apiKey = 'zZX3lCSmeyb0JN45fcMazf6Oq6mja6WXRwbwaQ9W';
    $count = request('count', 1); // Anzahl der gewünschten APOD-Einträge, Standard ist 1

    // Verwendung des Laravel Caching Systems
    $cacheKey = "apod_data_{$count}";
    $cacheDuration = now()->addHours(1); // Cache für 1 Stunde

    return Cache::remember($cacheKey, $cacheDuration, function () use ($apiKey, $count) {
        $response = Http::withoutVerifying()->get('https://api.nasa.gov/planetary/apod', [
            'api_key' => $apiKey,
            'count' => $count,
        ]);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json(['error' => 'Fehler beim Abrufen der APOD-Daten'], $response->status());
    });
});

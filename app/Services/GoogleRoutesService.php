<?php
namespace App\Services;

use GuzzleHttp\Client;

class GoogleRoutesService {
  protected Client $http;
  protected string $key;

  public function __construct(?Client $client = null) {
    $this->http = $client ?? new Client(['base_uri' => 'https://routes.googleapis.com/']);
    $this->key = config('services.google.routes_key', env('GOOGLE_ROUTES_API_KEY'));
  }

  /**
   * Calcula ruta óptima (Driving) usando ComputeRoutes de Google Routes API v2.
   * $origin ['lat'=>..,'lng'=>..], $dest igual. $stops array de waypoints.
   */
  public function computeRoute(array $origin, array $dest, array $stops = []): array {
    $payload = [
      'origin' => ['location'=>['latLng'=>$origin]],
      'destination' => ['location'=>['latLng'=>$dest]],
      'intermediates' => array_map(fn($p)=>['location'=>['latLng'=>$p]], $stops),
      'travelMode' => 'DRIVE',
      'computeAlternativeRoutes' => false,
      'routingPreference' => 'TRAFFIC_AWARE',
      'polylineEncoding' => 'GEO_JSON_LINEString'
    ];

    $res = $this->http->post('computeRoutes', [
      'headers' => [
        'X-Goog-Api-Key' => $this->key,
        'X-Goog-FieldMask' => 'routes.distanceMeters,routes.duration,routes.polyline',
        'Content-Type' => 'application/json'
      ],
      'json' => $payload,
      'timeout' => 20
    ]);

    $data = json_decode((string)$res->getBody(), true);
    $route = $data['routes'][0] ?? [];
    return [
      'distance_m' => $route['distanceMeters'] ?? null,
      'duration_s' => isset($route['duration']) ? (int)preg_replace('/\D/','',$route['duration']) : null,
      'polyline'   => $route['polyline'] ?? null,
    ];
  }
}

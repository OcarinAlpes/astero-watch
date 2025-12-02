<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Service pour interagir avec l'API NASA NeoWs (Near Earth Object Web Service)
 * 
 * @see https://api.nasa.gov/
 */
class NasaNeoApiService
{
    private const BASE_URL = 'https://api.nasa.gov/neo/rest/v1';

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $apiKey
    ) {
    }

    /**
     * Récupère les astéroïdes proches de la Terre pour une période donnée
     * 
     * @param \DateTimeInterface $startDate Date de début
     * @param \DateTimeInterface|null $endDate Date de fin (max 7 jours après start)
     * @return array Données des NEOs
     * @throws \Exception En cas d'erreur API
     */
    public function getFeed(\DateTimeInterface $startDate, ?\DateTimeInterface $endDate = null): array
    {
        $params = [
            'start_date' => $startDate->format('Y-m-d'),
            'api_key' => $this->apiKey,
        ];

        if ($endDate) {
            $params['end_date'] = $endDate->format('Y-m-d');
        }

        return $this->request('GET', '/feed', $params);
    }

    /**
     * Récupère les détails d'un astéroïde spécifique
     * 
     * @param int $asteroidId SPK-ID de l'astéroïde (NASA JPL small body ID)
     * @return array Données détaillées de l'astéroïde
     */
    public function getAsteroid(int $asteroidId): array
    {
        return $this->request('GET', "/neo/{$asteroidId}", [
            'api_key' => $this->apiKey,
        ]);
    }

    /**
     * Parcourt l'ensemble des astéroïdes (paginé)
     * 
     * @param int $page Numéro de page (0-indexed)
     * @param int $size Nombre d'éléments par page
     * @return array Liste paginée des astéroïdes
     */
    public function browse(int $page = 0, int $size = 20): array
    {
        return $this->request('GET', '/neo/browse', [
            'page' => $page,
            'size' => $size,
            'api_key' => $this->apiKey,
        ]);
    }

    /**
     * Effectue une requête HTTP vers l'API NASA
     */
    private function request(string $method, string $endpoint, array $params = []): array
    {
        $url = self::BASE_URL . $endpoint;

        $this->logger->info('NASA API Request', [
            'method' => $method,
            'endpoint' => $endpoint,
        ]);

        try {
            $response = $this->httpClient->request($method, $url, [
                'query' => $params,
            ]);

            $data = $response->toArray();

            $this->logger->info('NASA API Response OK', [
                'status' => $response->getStatusCode(),
            ]);

            return $data;

        } catch (\Exception $e) {
            $this->logger->error('NASA API Error', [
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
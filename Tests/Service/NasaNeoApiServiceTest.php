<?php

namespace App\Tests\Service;

use App\Service\NasaNeoApiService;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class NasaNeoApiServiceTest extends TestCase
{
    private function createService(array $responses): NasaNeoApiService
    {
        $mockClient = new MockHttpClient($responses);
        return new NasaNeoApiService($mockClient, new NullLogger(), 'fake_api_key');
    }

    public function testGetFeedReturnsData(): void
    {
        $mockData = [
            'element_count' => 2,
            'near_earth_objects' => [
                '2024-01-15' => [
                    ['id' => '123', 'name' => 'Test Asteroid'],
                ],
            ],
        ];

        $service = $this->createService([
            new MockResponse(json_encode($mockData)),
        ]);

        $result = $service->getFeed(new \DateTime('2024-01-15'));

        $this->assertArrayHasKey('element_count', $result);
        $this->assertEquals(2, $result['element_count']);
    }

    public function testGetAsteroidReturnsDetails(): void
    {
        $mockData = [
            'id' => '3542519',
            'name' => '(2010 PK9)',
            'is_potentially_hazardous_asteroid' => false,
        ];

        $service = $this->createService([
            new MockResponse(json_encode($mockData)),
        ]);

        $result = $service->getAsteroid(3542519);

        $this->assertEquals('3542519', $result['id']);
        $this->assertEquals('(2010 PK9)', $result['name']);
    }

    public function testBrowseReturnsPaginatedData(): void
    {
        $mockData = [
            'near_earth_objects' => [],
            'page' => [
                'size' => 20,
                'total_elements' => 100,
                'total_pages' => 5,
                'number' => 0,
            ],
        ];

        $service = $this->createService([
            new MockResponse(json_encode($mockData)),
        ]);

        $result = $service->browse(0, 20);

        $this->assertArrayHasKey('page', $result);
        $this->assertEquals(5, $result['page']['total_pages']);
    }
}
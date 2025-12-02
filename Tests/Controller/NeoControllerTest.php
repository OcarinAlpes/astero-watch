<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NeoControllerTest extends WebTestCase
{
    public function testIndexPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/neo');

        // L'API peut échouer, mais la page doit se charger
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Astéroïdes proches de la Terre');
    }

    public function testBrowsePageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/neo/browse');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Explorer tous les astéroïdes');
    }

    public function testShowPageWithInvalidId(): void
    {
        $client = static::createClient();
        $client->request('GET', '/neo/999999999');

        $this->assertResponseStatusCodeSame(404);
    }
}
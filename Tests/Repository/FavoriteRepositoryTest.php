<?php

namespace App\Tests\Repository;

use App\Entity\Favorite;
use App\Repository\FavoriteRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FavoriteRepositoryTest extends KernelTestCase
{
    private FavoriteRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->repository = static::getContainer()->get(FavoriteRepository::class);
    }

    public function testSaveAndFind(): void
    {
        $favorite = new Favorite();
        $favorite->setAsteroidId(12345);
        $favorite->setName('Test Asteroid');
        $favorite->setIsHazardous(true);
        $favorite->setAddedAt(new \DateTimeImmutable());

        $this->repository->save($favorite, true);

        $found = $this->repository->findByAsteroidId(12345);
        
        $this->assertNotNull($found);
        $this->assertEquals('Test Asteroid', $found->getName());
        $this->assertTrue($found->isHazardous());

        // Cleanup
        $this->repository->remove($found, true);
    }

    public function testIsFavorite(): void
    {
        $favorite = new Favorite();
        $favorite->setAsteroidId(99999);
        $favorite->setName('Test');
        $favorite->setIsHazardous(false);
        $favorite->setAddedAt(new \DateTimeImmutable());

        $this->repository->save($favorite, true);

        $this->assertTrue($this->repository->isFavorite(99999));
        $this->assertFalse($this->repository->isFavorite(88888));

        // Cleanup
        $this->repository->remove($favorite, true);
    }
}
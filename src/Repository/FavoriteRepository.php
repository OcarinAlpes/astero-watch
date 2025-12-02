<?php

namespace App\Repository;

use App\Entity\Favorite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Favorite>
 */
class FavoriteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Favorite::class);
    }

    /**
     * Trouve un favori par son ID d'astéroïde NASA
     */
    public function findByAsteroidId(int $asteroidId): ?Favorite
    {
        return $this->findOneBy(['asteroidId' => $asteroidId]);
    }

    /**
     * Vérifie si un astéroïde est déjà en favori
     */
    public function isFavorite(int $asteroidId): bool
    {
        return $this->findByAsteroidId($asteroidId) !== null;
    }

    /**
     * Récupère tous les favoris dangereux
     * 
     * @return Favorite[]
     */
    public function findHazardous(): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.isHazardous = :hazardous')
            ->setParameter('hazardous', true)
            ->orderBy('f.addedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre de favoris
     */
    public function countAll(): int
    {
        return $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Recherche par nom (partiel)
     * 
     * @return Favorite[]
     */
    public function searchByName(string $term): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.name LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('f.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Sauvegarde une entité
     */
    public function save(Favorite $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime une entité
     */
    public function remove(Favorite $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
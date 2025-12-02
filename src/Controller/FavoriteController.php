<?php

namespace App\Controller;

use App\Entity\Favorite;
use App\Form\FavoriteType;
use App\Repository\FavoriteRepository;
use App\Service\NasaNeoApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/favorites', name: 'favorite_')]
class FavoriteController extends AbstractController
{
    public function __construct(
        private FavoriteRepository $favoriteRepository,
        private NasaNeoApiService $nasaApi
    ) {
    }

    /**
     * Liste des favoris
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $favorites = $this->favoriteRepository->findBy([], ['addedAt' => 'DESC']);

        return $this->render('favorite/index.html.twig', [
            'favorites' => $favorites,
            'hazardous_count' => $this->favoriteRepository->count(['isHazardous' => true]),
        ]);
    }

    /**
     * Ajouter un astéroïde aux favoris
     */
    #[Route('/add/{asteroidId}', name: 'add', methods: ['GET', 'POST'])]
    public function add(int $asteroidId, Request $request): Response
    {
        // Vérifie si déjà en favori
        if ($this->favoriteRepository->isFavorite($asteroidId)) {
            $this->addFlash('warning', 'Cet astéroïde est déjà dans tes favoris');
            return $this->redirectToRoute('favorite_index');
        }

        // Récupère les données de l'API
        try {
            $asteroidData = $this->nasaApi->getAsteroid($asteroidId);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Impossible de récupérer les données de l\'astéroïde');
            return $this->redirectToRoute('neo_index');
        }

        // Pré-remplit l'entité
        $favorite = new Favorite();
        $favorite->setAsteroidId($asteroidId);
        $favorite->setName($asteroidData['name']);
        $favorite->setIsHazardous($asteroidData['is_potentially_hazardous_asteroid'] ?? false);

        $form = $this->createForm(FavoriteType::class, $favorite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->favoriteRepository->save($favorite, true);

            $this->addFlash('success', sprintf('"%s" ajouté aux favoris !', $favorite->getName()));
            return $this->redirectToRoute('favorite_index');
        }

        return $this->render('favorite/add.html.twig', [
            'form' => $form,
            'asteroid' => $asteroidData,
        ]);
    }

    /**
     * Modifier les notes d'un favori
     */
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Favorite $favorite, Request $request): Response
    {
        $form = $this->createForm(FavoriteType::class, $favorite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->favoriteRepository->save($favorite, true);

            $this->addFlash('success', 'Notes mises à jour !');
            return $this->redirectToRoute('favorite_index');
        }

        return $this->render('favorite/edit.html.twig', [
            'form' => $form,
            'favorite' => $favorite,
        ]);
    }

    /**
     * Supprimer un favori
     */
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Favorite $favorite, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete' . $favorite->getId(), $request->request->get('_token'))) {
            $this->favoriteRepository->remove($favorite, true);
            $this->addFlash('success', 'Favori supprimé');
        }

        return $this->redirectToRoute('favorite_index');
    }
}
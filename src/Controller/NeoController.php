<?php

namespace App\Controller;

use App\Service\NasaNeoApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/neo', name: 'neo_')]
class NeoController extends AbstractController
{
    public function __construct(
        private NasaNeoApiService $nasaApi
    ) {
    }

    /**
     * Page d'accueil - Liste des NEOs du jour
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $startDate = new \DateTime($request->query->get('start_date', 'today'));
        $endDate = new \DateTime($request->query->get('end_date', 'today'));

        // Limiter à 7 jours max (contrainte API)
        $interval = $startDate->diff($endDate);
        if ($interval->days > 7) {
            $endDate = (clone $startDate)->modify('+7 days');
        }

        try {
            $data = $this->nasaApi->getFeed($startDate, $endDate);
            
            return $this->render('neo/index.html.twig', [
                'neo_data' => $data,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'element_count' => $data['element_count'] ?? 0,
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la récupération des données NASA');
            
            return $this->render('neo/index.html.twig', [
                'neo_data' => null,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'element_count' => 0,
            ]);
        }
    }

    /**
     * Détails d'un astéroïde
     */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        try {
            $asteroid = $this->nasaApi->getAsteroid($id);

            return $this->render('neo/show.html.twig', [
                'asteroid' => $asteroid,
            ]);
        } catch (\Exception $e) {
            throw $this->createNotFoundException('Astéroïde non trouvé');
        }
    }

    /**
     * Navigation dans tous les astéroïdes (paginé)
     */
    #[Route('/browse', name: 'browse', methods: ['GET'])]
    public function browse(Request $request): Response
    {
        $page = max(0, $request->query->getInt('page', 0));
        $size = min(20, max(5, $request->query->getInt('size', 20)));

        $data = $this->nasaApi->browse($page, $size);

        return $this->render('neo/browse.html.twig', [
            'asteroids' => $data['near_earth_objects'] ?? [],
            'page' => $data['page'] ?? [],
            'current_page' => $page,
        ]);
    }
}
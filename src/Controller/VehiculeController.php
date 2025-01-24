<?php

namespace App\Controller;

use App\Entity\Vehicule;
use App\Form\VehiculeType;
use App\Repository\ReservationRepository;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/vehicule')]
final class VehiculeController extends AbstractController
{
    #[Route(name: 'app_vehicule_index', methods: ['GET'])]
    public function index(VehiculeRepository $vehiculeRepository, ReservationRepository $reservationRepository): Response
    {
        $vehicules = $vehiculeRepository->findAll();

        // Compter les réservations pour chaque véhicule
        $vehiculeReservations = [];
        foreach ($vehicules as $vehicule) {
            $vehiculeReservations[$vehicule->getId()] = $reservationRepository->countReservationsByVehicule($vehicule);
        }

        return $this->render('vehicule/index.html.twig', [
            'vehicules' => $vehicules,
            'vehiculeReservations' => $vehiculeReservations,
        ]);
    }

    #[Route('/new', name: 'app_vehicule_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $vehicule = new Vehicule();
        $form = $this->createForm(VehiculeType::class, $vehicule);

        $form->remove('immatricule');
        $form->remove('statut');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $vehicule->setStatut(1); // Disponible par défaut
            $vehicule->setImg("1.jpeg"); // Image par défaut
            $vehicule->setImmatricule($this->matricule($vehicule->getMarque()));

            $entityManager->persist($vehicule);
            $entityManager->flush();

            return $this->redirectToRoute('app_vehicule_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vehicule/new.html.twig', [
            'vehicule' => $vehicule,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_vehicule_show', methods: ['GET'])]
    public function show(Vehicule $vehicule, ReservationRepository $reservationRepository): Response
    {
        // Récupérer le nombre total de réservations pour ce véhicule
        $reservationCount = $reservationRepository->countReservationsByVehicule($vehicule);

        return $this->render('vehicule/show.html.twig', [
            'vehicule' => $vehicule,
            'reservationCount' => $reservationCount,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vehicule_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Vehicule $vehicule, EntityManagerInterface $entityManager): Response
    {
        // Vérifiez si le statut peut être modifié
        if (!$vehicule->canModifyStatut()) {
            $this->addFlash('error', 'Le statut ne peut pas être modifié tant qu’une réservation active est en cours.');
            return $this->redirectToRoute('app_vehicule_index');
        }

        $form = $this->createForm(VehiculeType::class, $vehicule, [
            'can_modify_statut' => $vehicule->canModifyStatut(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_vehicule_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vehicule/edit.html.twig', [
            'vehicule' => $vehicule,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_vehicule_delete', methods: ['POST'])]
    public function delete(Request $request, Vehicule $vehicule, EntityManagerInterface $entityManager): Response
    {
        // Vérifiez le token CSRF
        if ($this->isCsrfTokenValid('delete' . $vehicule->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($vehicule);
                $entityManager->flush();

                $this->addFlash('success', 'Le véhicule a été supprimé avec succès.');
            } catch (\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException $e) {
                // Exception pour contrainte de clé étrangère
                $this->addFlash('error', 'Impossible de supprimer ce véhicule car il est associé à des commentaires ou des réservations.');
            } catch (\Exception $e) {
                // Exception générique
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression du véhicule.');
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide. Action non autorisée.');
        }

        return $this->redirectToRoute('app_vehicule_index');
    }

    private function matricule($marque): string
    {
        $str = substr($marque, 0, 2) . "-";

        for ($i = 0; $i < 4; $i++) {
            $str .= chr(rand(0, 25) + 65);
        }

        $str .= '-';

        for ($i = 0; $i < 3; $i++) {
            $str .= rand(0, 9);
        }

        return $str;
    }
}

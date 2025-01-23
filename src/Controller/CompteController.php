<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Reservation;
use App\Entity\Vehicule;
use App\Form\ReservationType;
use Symfony\Component\HttpFoundation\Request;

#[Route('/client')]
final class CompteController extends AbstractController
{

    

    #[Route('/vehicule/{id}', name: 'app_client_vehicule')]
    public function client(Vehicule $vehicule, Request $request, EntityManagerInterface $manager): Response
    {
        $reserver = new Reservation;
        $commentaires = $vehicule->getCommentaires();
        $form = $this->createForm(ReservationType::class, $reserver);

        $form->remove('prix');
        $form->remove('vehicule');
        $form->remove('client');

        $form->handleRequest($request);

        if( $form->isSubmitted() ){
            $reserver->setPrix( $request->get('prixReservation') );
            $reserver->setClient($this->getUser());
            $reserver->setVehicule($vehicule);

            $manager->persist($reserver);
            $manager->flush();
        }

        //if ($formCommentaire->isSubmitted)   )
        /*{
            $commentaires...
        }*/
        
        return $this->render('home/reserver.html.twig', [
            "vehicule" => $vehicule,
            "commentaires" => $commentaires,
            "form" => $form
        ]);
    }
}

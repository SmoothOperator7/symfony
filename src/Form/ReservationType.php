<?php

namespace App\Form;

use App\Entity\Commentaire;
use App\Entity\Reservation;
use App\Entity\User;
use App\Entity\Vehicule;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_debut', DateType::class, [
                'widget' => 'single_text', // Permet d'utiliser un calendrier HTML5
                'html5' => true,
                'attr' => [
                    'class' => 'form-control',
                    'data-reserved-dates' => json_encode($options['reserved_dates']), // Passer les dates réservées au frontend
                ],
            ])
            ->add('date_fin', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control',
                    'data-reserved-dates' => json_encode($options['reserved_dates']), // Passer les dates réservées au frontend
                ],
            ])
            ->add('prix') // Conserve le champ prix
            ->add('vehicule', EntityType::class, [
                'class' => Vehicule::class,
                'choice_label' => 'id', // Affiche l'ID du véhicule comme label
            ])
            ->add('client', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id', // Affiche l'ID du client comme label
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
            'reserved_dates' => [], // Par défaut, aucune date réservée
        ]);
    }
}

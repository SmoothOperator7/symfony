<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Entity\Vehicule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    // Compter le nombre de réservations pour un véhicule donné
    public function countReservationsByVehicule(Vehicule $vehicule): int
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.vehicule = :vehicule')
            ->setParameter('vehicule', $vehicule)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

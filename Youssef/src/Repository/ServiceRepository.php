<?php

namespace App\Repository;

use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Service>
 *
 * @method Service|null find($id, $lockMode = null, $lockVersion = null)
 * @method Service|null findOneBy(array $criteria, array $orderBy = null)
 * @method Service[]    findAll()
 * @method Service[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Service::class);
    }

//    /**
//     * @return Service[] Returns an array of Service objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }
    /**
     * Recherche les services par nom ou catégorie.
     *
     * @param string $term Le terme de recherche
     * @return Service[] Retourne un tableau de services correspondant au terme de recherche
     */
    public function searchByNameOrCategory(string $term): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.nom LIKE :term')
            ->orWhere('s.categorie LIKE :term')
            ->setParameter('term', '%'.$term.'%')
            ->getQuery()
            ->getResult();
    }public function sortBy(string $sortBy, string $sortOrder): array
{
    return $this->createQueryBuilder('s')
        ->orderBy('s.' . $sortBy, $sortOrder)
        ->getQuery()
        ->getResult();
}
//    public function findOneBySomeField($value): ?Service
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

public function findByExampleField($value): array
   {
        return $this->createQueryBuilder('p')
                   ->where('p.nom LIKE :searchQuery')
                   ->orWhere('p.categorie LIKE :searchQuery')
                   ->orWhere('p.disponibilite LIKE :searchQuery')
                   ->setParameter('searchQuery', '%' . $value . '%')
                   ->getQuery()
                   ->getResult();
    }
}

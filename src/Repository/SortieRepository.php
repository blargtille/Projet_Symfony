<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraints\Date;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function save(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function findSortieByDate($dateDebut, $dateFin)
    {
        // avec QueryBuilder
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->addSelect('s');
        $queryBuilder->andWhere('s.dateHeureDebut > :dateDebut');
        $queryBuilder->andWhere('s.dateHeureDebut < :dateFin');
        $queryBuilder->setParameter('dateDebut', $dateDebut);
        $queryBuilder->setParameter('dateFin', $dateFin);

        $query = $queryBuilder->getQuery();

        $paginator = new Paginator($query);

        return $paginator;

    }

    public function findSortieByNameResearch($recherche)
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->addSelect('s');
        $queryBuilder->andWhere('s.nom LIKE :recherche');
        $queryBuilder->setParameter('recherche', '%' . $recherche . '%');

        $query = $queryBuilder->getQuery();

        $paginator = new Paginator($query);

        return $paginator;

    }

}

/*
    public function findSortieByCaseACocher($orga, $inscrit, $nonInscrit, $passees){
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->addSelect('s');
        $queryBuilder->andWhere('s.nom LIKE :recherche');
        $queryBuilder->setParameter('recherche', '%' . $recherche . '%');

        $query = $queryBuilder->getQuery();

        $paginator = new Paginator($query);

        return $paginator;

    }
*/


//    /**
//     * @return Sortie[] Returns an array of Sortie objects
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

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


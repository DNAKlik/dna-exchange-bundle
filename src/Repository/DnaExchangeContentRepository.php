<?php

namespace DnaKlik\DnaExchangeBundle\Repository;

use DnaKlik\DnaExchangeBundle\Entity\DnaExchangeContent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DnaExchangeContent>
 *
 * @method DnaExchangeContent|null find($id, $lockMode = null, $lockVersion = null)
 * @method DnaExchangeContent|null findOneBy(array $criteria, array $orderBy = null)
 * @method DnaExchangeContent[]    findAll()
 * @method DnaExchangeContent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DnaExchangeContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DnaExchangeContent::class);
    }

    public function StampsInContent($criteria, $order, $limit, $offset) {
        $result = $this->findBy($criteria,$order,$limit,$offset);
        return $result;
    }

//    /**
//     * @return DnaExchangeContent[] Returns an array of DnaExchangeContent objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DnaExchangeContent
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

<?php

namespace DnaKlik\DnaExchangeBundle\Repository;

use DnaKlik\DnaExchangeBundle\Entity\DnaExchangeContentStamp;
use DnaKlik\DnaExchangeBundle\Entity\DnaExchangeContent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DnaExchangeContentStamp>
 *
 * @method DnaExchangeContentStamp|null find($id, $lockMode = null, $lockVersion = null)
 * @method DnaExchangeContentStamp|null findOneBy(array $criteria, array $orderBy = null)
 * @method DnaExchangeContentStamp[]    findAll()
 * @method DnaExchangeContentStamp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DnaExchangeContentStampRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DnaExchangeContentStamp::class);
    }


    public function countTotalStamps($stamp)
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->select('count(d.Stamp) As stampCount')
            ->where('d.Stamp = :val')
            ->setParameter('val', $stamp)
            ->getQuery();
        $result = $queryBuilder->getOneOrNullResult();
        return $result["stampCount"];
    }

    public function countStampInContent($stamp)
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->select('c.id, count(d.Stamp) As stampCount')
            ->join('DnaKlik\DnaExchangeBundle\Entity\DnaExchangeContent','c', \Doctrine\ORM\Query\Expr\Join::WITH, 'd.dnaExchangeContent = c.id')
            ->where('d.Stamp = :val')
            ->groupBy('d.dnaExchangeContent')
            ->setParameter('val', $stamp)
            ->getQuery();
        $result = $queryBuilder->getResult();
        return $result;
    }

    public function countTotalStampsInContent($id)
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->select('c.id, count(d.Stamp) As stampCount')
            ->join('DnaKlik\DnaExchangeBundle\Entity\DnaExchangeContent','c', \Doctrine\ORM\Query\Expr\Join::WITH, 'd.dnaExchangeContent = c.id')
            ->where('c.id = :val')
            ->groupBy('d.dnaExchangeContent')
            ->setParameter('val', $id)
            ->getQuery();
        $result = $queryBuilder->getOneOrNullResult();
        return $result["stampCount"];
    }

    public function StampsInContent()
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->select('c.id AS id, c.slug AS slug, c.stamp AS stamp, count(d.Stamp) As stampCount')
            ->join('DnaKlik\DnaExchangeBundle\Entity\DnaExchangeContent','c', \Doctrine\ORM\Query\Expr\Join::WITH, 'd.dnaExchangeContent = c.id')
            ->groupBy('d.dnaExchangeContent')
            ->orderBy('stampCount', 'DESC')
            ->getQuery();
        $result = $queryBuilder->getResult();
        //dump($result);
        foreach($result as $ind => $item) {
            $queryBuilder = $this->createQueryBuilder('d')
                ->select('d.id, d.Stamp')
                ->where('d.dnaExchangeContent= :val1')
                ->setParameter('val1', $item["id"])
                ->getQuery();

            $itemResult = $queryBuilder->getResult();
            //dump($itemResult);
            foreach($itemResult as $resValue) {
                if(isset($result[$ind]["dnaArr"][$resValue["Stamp"]])) {
                    $result[$ind]["dnaArr"][$resValue["Stamp"]]++;
                }
                else {
                    $result[$ind]["dnaArr"][$resValue["Stamp"]] = 1;
                }
            }
            arsort($result[$ind]["dnaArr"]);
        }
        return $result;
    }

//    /**
//     * @return DnaExchangeContentStamp[] Returns an array of DnaExchangeContentStamp objects
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

//    public function findOneBySomeField($value): ?DnaExchangeContentStamp
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

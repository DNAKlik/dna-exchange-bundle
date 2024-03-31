<?php

namespace DnaKlik\DnaExchangeBundle\Repository;

use DnaKlik\DnaExchangeBundle\Entity\DnaExchangeUserStamp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DnaExchangeUserStamp>
 *
 * @method DnaExchangeUserStamp|null find($id, $lockMode = null, $lockVersion = null)
 * @method DnaExchangeUserStamp|null findOneBy(array $criteria, array $orderBy = null)
 * @method DnaExchangeUserStamp[]    findAll()
 * @method DnaExchangeUserStamp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DnaExchangeUserStampRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DnaExchangeUserStamp::class);
    }

    public function countTotalStamps($stamp) {
        $queryBuilder = $this->createQueryBuilder('d')
            ->select('count(d.Stamp) As stampCount')
            ->where('d.Stamp = :val')
            ->setParameter('val', $stamp)
            ->getQuery();
        $result = $queryBuilder->getOneOrNullResult();
        return $result["stampCount"];
    }

    public function countStampInUser($stamp)
    {
        // dump($stamp);
        $queryBuilder = $this->createQueryBuilder('d')
            ->select('u.id AS userId, p.id AS profileId, count(d.Stamp) As stampCount')
            ->join('App\Entity\User','u', \Doctrine\ORM\Query\Expr\Join::WITH, 'd.user = u.id')
            ->leftJoin('App\Entity\UserProfile','p', \Doctrine\ORM\Query\Expr\Join::WITH, 'd.profile = p.id')
            ->where('d.Stamp = :val')
            ->groupBy('d.user, d.profile')
            ->setParameter('val', $stamp)
            ->getQuery();
        $result = $queryBuilder->getResult();
        //dump($result);
        return $result;
    }

    public function getStampsFromUser($id_user, $id_profile) {

        if ($id_profile == 0) {
            $queryBuilder = $this->createQueryBuilder('d')
                ->select('d.Counter AS stampPlace, d.Stamp')
                ->join('App\Entity\User','u', \Doctrine\ORM\Query\Expr\Join::WITH, 'd.user = u.id')
                ->leftJoin('App\Entity\UserProfile','p', \Doctrine\ORM\Query\Expr\Join::WITH, 'd.profile = p.id')
                ->where('u.id = :val1 AND d.profile IS NULL')
                ->setParameter('val1', $id_user)
                ->getQuery();
        }
        else {

            $queryBuilder = $this->createQueryBuilder('d')
                ->select('d.Counter AS stampPlace, d.Stamp')
                ->join('App\Entity\User','u', \Doctrine\ORM\Query\Expr\Join::WITH, 'd.user = u.id')
                ->leftJoin('App\Entity\UserProfile','p', \Doctrine\ORM\Query\Expr\Join::WITH, 'd.profile = p.id')
                ->where('u.id = :val1 AND p.id = :val2')
                ->setParameter('val1', $id_user)
                ->setParameter('val2', $id_profile)
                ->getQuery();
        }
        $result = $queryBuilder->getResult();
        return $result;
    }

    public function countTotalStampsInUserProfile($user_id, $profile_id) {
        if ($profile_id == 0) {
            $queryBuilder = $this->createQueryBuilder('d')
                ->select('u.id AS userId, u.name AS userName, count(d.Stamp) As stampCount')
                ->join('App\Entity\User','u', \Doctrine\ORM\Query\Expr\Join::WITH, 'd.user = u.id')
                ->where('d.profile IS NULL and u.id = :val')
                ->groupBy('d.user')
                ->setParameter('val', $user_id)
                ->getQuery();
            $result = $queryBuilder->getOneOrNullResult();
        }
        else {
            $queryBuilder = $this->createQueryBuilder('d')
                ->select('u.id AS userId, u.name AS userName, p.id AS profileId, p.name AS profileName, count(d.Stamp) As stampCount')
                ->join('App\Entity\User','u', \Doctrine\ORM\Query\Expr\Join::WITH, 'd.user = u.id')
                ->leftJoin('App\Entity\UserProfile','p', \Doctrine\ORM\Query\Expr\Join::WITH, 'd.profile = p.id')
                ->where('u.id = :val1 AND p.id = :val2')
                ->groupBy('d.user, d.profile')
                ->setParameter('val1', $user_id)
                ->setParameter('val2', $profile_id)
                ->getQuery();
            $result = $queryBuilder->getOneOrNullResult();
        }
        return $result;
    }

    public function StampsInProfile()
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->select('COUNT(d.id) as count, u.id AS userid, u.name AS username, p.id AS profileid, p.name AS profilename')
            ->join('App\Entity\User','u', \Doctrine\ORM\Query\Expr\Join::WITH, 'd.user = u.id')
            ->leftJoin('App\Entity\UserProfile','p', \Doctrine\ORM\Query\Expr\Join::WITH, 'd.profile = p.id')
            ->groupBy('d.user, d.profile')
            ->getQuery();
        $result = $queryBuilder->getResult();
        //dump($result);
        foreach($result as $ind => $item) {
            //dump($item);
            $dnaString = "";
            if (is_null($item["profileid"])) {
                $result[$ind]["profile"] = "";
                $queryBuilder = $this->createQueryBuilder('d')
                    ->select('d.id, d.Stamp')
                    ->where('d.user= :val1')
                    ->andWhere('d.profile IS NULL')
                    ->setParameter('val1', $item["userid"])
                    ->getQuery();
            }
            else {
                $queryBuilder = $this->createQueryBuilder('d')
                    ->select('d.id, d.Stamp')
                    ->where('d.user= :val1 AND d.profile= :val2')
                    ->setParameter('val1', $item["userid"])
                    ->setParameter('val2', $item["profileid"])
                    ->getQuery();
            }
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
//     * @return DnaExchangeUserStamp[] Returns an array of DnaExchangeUserStamp objects
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

//    public function findOneBySomeField($value): ?DnaExchangeUserStamp
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

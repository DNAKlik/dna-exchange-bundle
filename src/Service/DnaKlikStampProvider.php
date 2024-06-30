<?php
namespace DnaKlik\DnaExchangeBundle\Service;

use DnaKlik\DnaExchangeBundle\Repository\DnaExchangeContentRepository;
use DnaKlik\DnaExchangeBundle\Repository\DnaExchangeContentStampRepository;
use DnaKlik\DnaExchangeBundle\Repository\DnaExchangeUserStampRepository;
use DnaKlik\DnaExchangeBundle\Entity\DnaExchangeContent;
use DnaKlik\DnaExchangeBundle\Entity\DnaExchangeContentStamp;
use DnaKlik\DnaExchangeBundle\Entity\DnaExchangeUserStamp;
use DnaKlik\DnaExchangeBundle\Service\MatchDna;
use DnaKlik\DnaExchangeBundle\Service\StampsCrossOver;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DnaKlikStampProvider implements StampProviderInterface
{
    public $dnaExchangeContentRepository;

    public $dnaExchangeContentStampRepository;

    private $dnaExchangeUserStampRepository;

    public $stampsCrossOver;

    public $matchDna;

    public $manager;

    public $user;

    public function __construct( DnaExchangeContentRepository $dnaExchangeContentRepository, DnaExchangeContentStampRepository $dnaExchangeContentStampRepository, DnaExchangeUserStampRepository $dnaExchangeUserStampRepository, MatchDna $matchDna, ManagerRegistry $manager, TokenStorageInterface $tokenStorageInterface, StampsCrossOver $stampsCrossOver) {
        $this->dnaExchangeContentRepository = $dnaExchangeContentRepository;
        $this->dnaExchangeUserStampRepository = $dnaExchangeUserStampRepository;
        $this->dnaExchangeContentStampRepository = $dnaExchangeContentStampRepository;
        $this->stampsCrossOver = $stampsCrossOver;
        $this->matchDna = $matchDna;
        $this->manager = $manager;
        if (is_null($tokenStorageInterface->getToken())) {
            $this->user = false;
        }
        else {
            $this->user = $tokenStorageInterface->getToken()->getUser();
        }
    }

    public function getContent(): array
    {
        $result = $this->dnaExchangeContentRepository->findBy(array(),array('id'=>'DESC'),3,0);
        return $result;
    }

    public function getContentFromId($id) {
        $result = $this->dnaExchangeContentRepository->findBy(array("id" => $id),array('id'=>'DESC'),3,0);
        return $result;
    }

    public function getUserContent($user_id, $profile_id) {
        return false;
    }

    public function getProfiles(): array
    {
        $result = $this->dnaExchangeUserStampRepository->StampsInProfile();
        return $result;
    }

    public function getItemStamps($criteria): array
    {
        $result = $this->dnaExchangeContentRepository->findOneBy($criteria);
        if (is_null($result) || count($result->getDnaExchangeContentStamp()) == 0) {
            $itemStamps = array();
        }
        else {
            foreach ($result->getDnaExchangeContentStamp() as $dnaExchangeContentStamp) {
                $itemStamps[] = $dnaExchangeContentStamp->getStamp();
            }
        }

        return $itemStamps;
    }

    public function getUserProfileStamps($id_user, $id_profile): array
    {
        $result = $this->dnaExchangeUserStampRepository->getStampsFromUser($id_user, $id_profile);
        foreach($result as $ind => $stampValues) {
            $itemStamps[$ind] = $stampValues["Stamp"];
        }
        return $itemStamps;
    }

    public function getUserStamps(): array
    {
        $userStamps = array();
        if ($this->user) {
            $profile = $this->user->getSelectedProfile();
            $result = $this->dnaExchangeUserStampRepository->findBy(array("user" => $this->user, "profile" => $profile));
            foreach($result as $dnaExchangeUserStamp) {
                $userStamps[] = $dnaExchangeUserStamp->getStamp();
            }
        }
        return $userStamps;
    }

    public function getStampsAfterCrossover($criteria, $userStamps): array
    {
        $result = $this->dnaExchangeContentRepository->findOneBy($criteria);
        if (count($result->getDnaExchangeContentStamp()) == 0) {
            $stampsCollection = $this->stampsCrossOver->crossOver($result, $userStamps, array());
        }
        else {
            foreach ($result->getDnaExchangeContentStamp() as $dnaExchangeContentStamp) {
                $itemStamps[] = $dnaExchangeContentStamp->getStamp();
            }
            $stampsCollection = $this->stampsCrossOver->crossOver($result, $userStamps, $itemStamps);
        }
        return $stampsCollection;
    }

    public function getStamp($criteria): string
    {
        $dnaExchangeContent = $this->dnaExchangeContentRepository->findOneBy($criteria);
        if (is_null($dnaExchangeContent)) {
            $results = $this->dnaExchangeContentRepository->findBy(array(),array('id'=>'DESC'),1,0);
            if (count($results) == 0) {
                $stamp = "0000";
                $dnaExchangeContent = new DnaExchangeContent();
                $dnaExchangeContent->setStamp("0000");
                $dnaExchangeContent->setSlug($criteria["slug"]);
                $entityManager =$this->manager->getManager();
                $entityManager->persist($dnaExchangeContent);
                $entityManager->flush();
            }
            else {
                foreach($results as $stampContent) {
                    $new_stamp = $this->new_stamp($stampContent->getStamp());
                    $stamp = $new_stamp;
                    $dnaExchangeContent = new DnaExchangeContent();
                    $dnaExchangeContent->setStamp($new_stamp);
                    $dnaExchangeContent->setSlug($criteria["slug"]);
                    $entityManager =$this->manager->getManager();
                    $entityManager->persist($dnaExchangeContent);
                    $entityManager->flush();
                }
            }
        }
        else {
            $stamp = $dnaExchangeContent->getStamp();
        }
        return $stamp;
    }

    public function getUser() {
        return $this->user;
    }

    public function findMatchItems($dna, $max)
    {
        $items = $this->matchDna->findMatchItems($dna, $max);
        return $items;
    }

    public function findMatchUsers($dna, $max)
    {
        $users = $this->matchDna->findMatchUsers($dna, $max);
        return $users;
    }

    protected function new_stamp( $stamp ) {
        $scale = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
        for($i=0; $i<strlen($stamp); $i++) {
            $key = array_search( $stamp[$i], $scale);
            $counter[$i] = $key;
        }
        $counter[3] += 1;
        for($i=3; $i>0; $i--) {
            if ($counter[$i] > 35) {
                $counter[$i-1]++;
                $counter[$i] = 0;
            }
        }
        $new_stamp = $scale[$counter[0]].$scale[$counter[1]].$scale[$counter[2]].$scale[$counter[3]];
        return $new_stamp;
    }
}

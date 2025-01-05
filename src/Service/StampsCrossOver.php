<?php

namespace DnaKlik\DnaExchangeBundle\Service;

use DnaKlik\DnaExchangeBundle\Repository\DnaExchangeContentStampRepository;
use DnaKlik\DnaExchangeBundle\Entity\DnaExchangeContentStamp;
use DnaKlik\DnaExchangeBundle\Entity\DnaExchangeUserStamp;
use DnaKlik\DnaExchangeBundle\Repository\DnaExchangeUserStampRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Authentication\Token\Storage\UsageTrackingTokenStorage;

class StampsCrossOver
{
    private $crossOver = 8;

    private $maxStamps = 64;

    private $user = false;

    private $dnaExchangeContentStampRepository;

    private $dnaExchangeUserStampRepository;

    private $entityManager;

    public function __construct( DnaExchangeUserStampRepository $dnaExchangeUserStampRepository, DnaExchangeContentStampRepository $dnaExchangeContentStampRepository, ManagerRegistry $manager, UsageTrackingTokenStorage $usageTrackingTokenStorage) {

        $this->dnaExchangeUserStampRepository = $dnaExchangeUserStampRepository;
        $this->dnaExchangeContentStampRepository = $dnaExchangeContentStampRepository;
        $this->entityManager = $manager->getManager();
        if (is_null($usageTrackingTokenStorage->getToken())) {
            $this->user = false;
        }
        else {
            $this->user = $usageTrackingTokenStorage->getToken()->getUser();
        }
    }

    public function setCrossOver($crossOver) {
        $this->crossOver = $crossOver;
    }

    public function setMaxStamps($maxStamps) {
        $this->maxStamps = $maxStamps;
    }
    public function crossOver($dnaStampContent, $userStamps, $itemStamps) {
        $itemParentStamp = $dnaStampContent->getStamp();
        //dump($userStamps);
        //dump($itemStamps);
        if (count($userStamps) < 1 && count($itemStamps) < 1) {
            // stop 4 moeder stamps van item in item
            for($i = 1;$i <= round($this->crossOver/2); $i++) {
                $parent_item_dna[$i] = $itemParentStamp;
            }
            $itemStamps = $this->addStampsToItem($dnaStampContent, $parent_item_dna, 0, $itemStamps);
            // $this->entityManager->flush();
            // stop 4 moeder stamps van item in user
            $userStamps = $this->addStampsToUser($parent_item_dna, 0, $userStamps);
            // $unitOfWork = $this->entityManager->getUnitOfWork();
            // $unitOfWork->commit($itemStamps);
            // $unitOfWork->commit($userStamps);
            $this->entityManager->flush();
            //dump($dnaStampContent);
        }
        elseif (count($userStamps) < 1 ) {
            // echo "geen stamps van user naar item";
            // stop 4 moeder stamps van item in user
            for($i = 1;$i <= round($this->crossOver/2); $i++) {
                $parent_item_dna[$i] = $itemParentStamp;
            }
            $userStamps = $this->addStampsToUser($parent_item_dna, 0, $userStamps);
            //dump($dnaStampContent);
            // stop 4 stamps van item in user
            $parent_item_array = array_chunk($this->shuffle_array($itemStamps), $this->crossOver, true);
            $parent_item_dna = $parent_item_array[0];
            $userCounter = count($userStamps);
            $userStamps = $this->addStampsToUser($parent_item_dna, $userCounter, $userStamps);
            if(count($itemStamps ) <= ($this->maxStamps - $this->crossOver/2)) {
                // stop 4 moeder stamps van item in item
                $itemCounter = count($itemStamps);
                $itemStamps = $this->addStampsToItem($dnaStampContent, $parent_item_dna, $itemCounter, $itemStamps);
            }
            // $unitOfWork = $this->entityManager->getUnitOfWork();
            // $unitOfWork->commit($itemStamps);
            // $unitOfWork->commit($userStamps);
            $this->entityManager->flush();
        }
        elseif (count($itemStamps ) < 1 ) {
            // stop 4 moeder stamps van item in item
            for($i = 1;$i <= round($this->crossOver/2); $i++) {
                $parentStamps[$i] = $itemParentStamp;
            }
            $itemStamps = $this->addStampsToItem($dnaStampContent, $parentStamps, 0, $itemStamps);
            // stop 4 stamps van user in item
            $parent_user_dna = $this->getParentArr($userStamps);
            $parent_user_array = array_chunk($parent_user_dna, $this->crossOver, true);
            //dump($parent_user_array);
            if (count($parent_user_array[0]) > 0) {
                for($i = 1;$i <= round($this->crossOver/2); $i++) {
                    $parent_user_array[0][$i] = $itemParentStamp;
                }
            }
            // dump($parent_user_array[0]);
            $itemCounter = count($itemStamps);
            $itemStamps = $this->addStampsToItem($dnaStampContent, $parent_user_array[0], $itemCounter, $itemStamps);
            // stop 4 moeder stamps van item in user als user nog niet vol is
            // anders niets doen, want er zijn al 4 stamps van user in item geplaatst dus de match is er al
            $stampsCounter = count($userStamps);
            //dump($parent_user_dna);
            foreach($parent_user_dna as $ind => $stamp) {
                $parentIndexArr[] = $ind;
            }
            //dump($parentIndexArr);
            shuffle($parentIndexArr);
            //dump($parentIndexArr);
            $counter = 0;
            for($i = 1;$i <= round($this->crossOver/2); $i++) {
                if ($stampsCounter < $this->maxStamps) {
                    if ($this->user) {
                        $dnaExchangeUserStamp = new DnaExchangeUserStamp();
                        $dnaExchangeUserStamp->setUser($this->user);
                        if (!is_null($this->user->getSelectedProfile())) {
                            $dnaExchangeUserStamp->setProfile($this->user->getSelectedProfile());
                        }
                        $dnaExchangeUserStamp->setStamp($itemParentStamp);
                        $dnaExchangeUserStamp->setCounter($i+$stampsCounter);
                        $this->entityManager->persist($dnaExchangeUserStamp);
                    }
                    $userStamps[] = $itemParentStamp;
                }
                else {
                    // stop 4 moeder stamps in user op plek van de user parent stamps
                    if ($this->user) {
                        $dnaExchangeUserStamp = $this->dnaExchangeUserStampRepository->findOneBy(array("user" => $this->user, "profile" => $this->user->getSelectedProfile(), "Counter" => $parentIndexArr[$counter] + 1));
                        if (is_null($dnaExchangeUserStamp)) {
                            dump($parentIndexArr[$counter]);
                        }
                        $dnaExchangeUserStamp->setStamp($itemParentStamp);
                        $this->entityManager->persist($dnaExchangeUserStamp);
                    }
                    $userStamps[$parentIndexArr[$counter]] = $itemParentStamp;
                    $counter++;
                }
            }
            // $unitOfWork = $this->entityManager->getUnitOfWork();
            // $unitOfWork->commit($itemStamps);
            // $unitOfWork->commit($dnaExchangeUserStamp);
            // $unitOfWork->commit($dnaExchangeUserStamp);
            $this->entityManager->flush();
            //dump($userStamps);
            //dump($dnaStampContent);
        }
        else {
            $itemCounter = count($itemStamps);
            $userCounter = count($userStamps);
            $parent_user_dna = $this->getParentArr($userStamps);
            $parent_item_dna = $this->getParentArr($itemStamps);

            //dump($parent_item_dna);
            //dump($parent_user_dna);

            $child_item_dna = $this->createChildArr($parent_user_dna, $parent_item_dna);
            $child_user_dna = $this->createChildArr($parent_item_dna, $parent_user_dna);

            if ($itemCounter <= ($this->maxStamps - $this->crossOver)) {
                // array content_stamp is nog niet vol, dus child stamps item worden toegevoegd
                // er moeten nieuwe stamps toegevoegd worden aan item, we kunnen kiezen uit extra stamps van user, 4 extra moeder stamps of 4 random stamps al bestaande stamps dupliceren
                //dump($child_item_dna); // 8 stamps
                $child_item_dna_parts = array_chunk($child_item_dna, round($this->crossOver/2), true);
                //$child_dna = array_merge($child_item_array_chunks[0], $child_user_array_chunks[0]);
                // add 4 stamps to item
                $itemStamps = $this->addStampsToItem($dnaStampContent, $child_item_dna_parts[0], $itemCounter, $itemStamps);
                $itemCounter = count($itemStamps);
                // add 4 stamps to item
                $itemStamps = $this->addStampsToItem($dnaStampContent, $child_item_dna_parts[1], $itemCounter, $itemStamps);
            }
            elseif ($itemCounter < $this->maxStamps) {
                // array content stamps is nog niet vol, dus child stamps user worden toegevoegd
                $i = 1;
                foreach($parent_item_dna as $ind => $stamp) {
                    $parentIndexArr[] = $ind;
                }
                $counter = 0;
                foreach ($child_item_dna as $stamp) {
                    if (($i + $itemCounter) <= $this->maxStamps) {
                        $dnaExchangeContentStamp = new DnaExchangeContentStamp();
                        $dnaExchangeContentStamp->setDnaExchangeContent($dnaStampContent);
                        $dnaExchangeContentStamp->setStamp($stamp);
                        $dnaExchangeContentStamp->setCounter($itemCounter+$i);
                        $dnaStampContent->addDnaExchangeContentStamp($dnaExchangeContentStamp);
                        $itemStamps[] = $stamp;
                        $i++;
                    }
                    else {
                        $dnaExchangeContentStamp = $this->dnaExchangeContentStampRepository->findOneBy(array("dnaExchangeContent" => $dnaStampContent, "Counter" => $parentIndexArr[$counter]+1));
                        $dnaExchangeContentStamp->setStamp($stamp);
                        $this->entityManager->persist($dnaExchangeContentStamp);
                        $itemStamps[$parentIndexArr[$counter]] = $stamp;
                    }
                    $counter++;
                }
            }
            else {
                $itemStamps = $this->updateStampsInItem($dnaStampContent, $parent_item_dna, $child_item_dna, $itemStamps);
            }
            if ($userCounter <= ($this->maxStamps - $this->crossOver)) {
                // array user stamps is nog niet vol, dus child stamps user worden toegevoegd
                $child_user_dna_parts = array_chunk($child_user_dna, round($this->crossOver/2), true);
                $userStamps = $this->addStampsToUser($child_user_dna_parts[0], $userCounter, $userStamps);
                $userCounter = count($userStamps);
                $userStamps = $this->addStampsToUser($child_user_dna_parts[1], $userCounter, $userStamps);
            }
            elseif ($userCounter < $this->maxStamps) {
                // array user stamps is nog niet vol, dus child stamps user worden toegevoegd
                $i = 1;
                foreach($parent_user_dna as $ind => $stamp) {
                    $parentIndexArr[] = $ind;
                }
                $counter = 0;
                foreach ($child_user_dna as $stamp) {
                    if ($this->user) {
                        if (($i + $userCounter) <= $this->maxStamps) {
                            $dnaExchangeUserStamp = new DnaExchangeUserStamp();
                            $dnaExchangeUserStamp->setUser($this->user);
                            if (!is_null($this->user->getSelectedProfile())) {
                                $dnaExchangeUserStamp->setProfile($this->user->getSelectedProfile());
                            }
                            $dnaExchangeUserStamp->setStamp($stamp);
                            $dnaExchangeUserStamp->setCounter($i + $userCounter);
                            $this->entityManager->persist($dnaExchangeUserStamp);
                            $userStamps[] = $stamp;
                            $i++;
                        }
                        else {
                            $dnaExchangeUserStamp = $this->dnaExchangeUserStampRepository->findOneBy(array("user" => $this->user, "profile" => $this->user->getSelectedProfile(), "Counter" => $parentIndexArr[$counter]+1));
                            if (is_null($dnaExchangeUserStamp)) {
                                dump($parentIndexArr[$counter]);
                                dump($this->user);
                            }
                            $dnaExchangeUserStamp->setStamp($stamp);
                            $this->entityManager->persist($dnaExchangeUserStamp);
                            $userStamps[$parentIndexArr[$counter]] = $stamp;
                        }
                    }
                    else {
                        if (($i + $userCounter) <= $this->maxStamps) {
                            $userStamps[] = $stamp;
                        }
                        else {
                            $userStamps[$parentIndexArr[$counter]] = $stamp;
                        }
                    }
                    $counter++;
                }
            }
            else {
                $userStamps = $this->updateStampsInUser($parent_user_dna, $child_user_dna, $userStamps);
            }
            // $unitOfWork = $this->entityManager->getUnitOfWork();
            // $unitOfWork->commit($itemStamps);
            // $unitOfWork->commit($userStamps);
            // $unitOfWork->commit($dnaExchangeUserStamp);
            // $unitOfWork->commit(($dnaExchangeUserStamp);
            $this->entityManager->flush();

        }
        if ($this->user) {
            if ($this->user->getSelectedProfile()) {
                $error = false;
                $stamps = $this->dnaExchangeUserStampRepository->findBy(array("user" => $this->user, "profile" => $this->user->getSelectedProfile()));
                // check counter values
                $counterArr = array();
                foreach ($stamps as $stamp) {
                    $counter = $stamp->getCounter();
                    if (in_array($counter, $counterArr)) {
                        dump("counter already excist: " . $counter);
                        dump("userstamps: " . count($userStamps) . " itemstamps: " . count($itemStamps));
                        $error = true;
                    }
                    $counterArr[] = $counter;
                }
                if ($error) {
                    exit();
                }
            }
        }
        $stampsCollection = array("userStamps" => $userStamps, "itemStamps" => $itemStamps);
        return $stampsCollection;
    }

    protected function addStampsToItem($dnaStampContent, $parentArr, $itemCounter, $itemStamps) {
        // stop 4 moeder stamps van item in item
        for($i = 1;$i <= round($this->crossOver/2); $i++) {
            $insert_stamp = array_shift($parentArr);
            $dnaExchangeContentStamp = new DnaExchangeContentStamp();
            $dnaExchangeContentStamp->setDnaExchangeContent($dnaStampContent);
            $dnaExchangeContentStamp->setStamp($insert_stamp);
            $dnaExchangeContentStamp->setCounter($itemCounter + $i);
            $dnaStampContent->addDnaExchangeContentStamp($dnaExchangeContentStamp);
            $this->entityManager->persist($dnaStampContent);
            $itemStamps[] = $insert_stamp;
        }
        return $itemStamps;
    }

    protected function addStampsToUser($itemStamps, $userCounter, $userStamps) {

        for($i = 1;$i <= round($this->crossOver/2); $i++) {
            $insert_stamp = array_shift($itemStamps);
            if ($this->user) {
                $dnaExchangeUserStamp = new DnaExchangeUserStamp();
                $dnaExchangeUserStamp->setUser($this->user);
                if (!is_null($this->user->getSelectedProfile())) {
                    $dnaExchangeUserStamp->setProfile($this->user->getSelectedProfile());
                }
                $dnaExchangeUserStamp->setStamp($insert_stamp);
                $dnaExchangeUserStamp->setCounter($userCounter+$i);
                $this->entityManager->persist($dnaExchangeUserStamp);
            }
            $userStamps[] = $insert_stamp;
        }
        return $userStamps;
    }

    protected function updateStampsInItem($dnaStampContent, $parent_item_dna, $child_item_dna, $itemStamps) {
        foreach($parent_item_dna as $ind => $stamp) {
            $parentIndexArr[] = $ind;
        }
        $counter = 0;
        foreach ($child_item_dna as $stamp) {
            $dnaExchangeContentStamp = $this->dnaExchangeContentStampRepository->findOneBy(array("dnaExchangeContent" => $dnaStampContent, "Counter" => $parentIndexArr[$counter]+1));
            $dnaExchangeContentStamp->setStamp($stamp);
            $this->entityManager->persist($dnaExchangeContentStamp);
            $itemStamps[$parentIndexArr[$counter]] = $stamp;
            $counter++;
        }
        return $itemStamps;
    }

    protected function updateStampsInUser($parent_user_dna, $child_user_dna, $userStamps) {
        foreach($parent_user_dna as $ind => $stamp) {
            $parentIndexArr[] = $ind;
        }
        $counter = 0;
        foreach ($child_user_dna as $stamp) {
            if ($this->user) {
                $dnaExchangeUserStamp = $this->dnaExchangeUserStampRepository->findOneBy(array("user" => $this->user, "profile" => $this->user->getSelectedProfile(), "Counter" => ($parentIndexArr[$counter]+1)));
                $dnaExchangeUserStamp->setStamp($stamp);
                $this->entityManager->persist($dnaExchangeUserStamp);
            }
            $userStamps[$parentIndexArr[$counter]] = $stamp;
            $counter++;
        }
        return $userStamps;
    }

    protected function shuffle_array($array) {
        //srand ((double) microtime() * 10000000);
        // index wordt niet behouden
        $caa=(count($array)-1);
        $new_array = array();
        for ($x=0 ; $x<=$caa ; $x++)
        {
            $ca = (count($array)-1);
            $i = rand(0,$ca);

            // zet geselecteerde waarde in nieuwe array, preserve key
            // get key

            $new_array[$x] = $array[$i];

            // schuif alles op, zodat geen lege waardes tussen zitten
            for ($t=$i ; $t<$ca ; $t++)
                $array[$t] = $array[$t+1];

            // gooi de laatste waarde van de array eruit
            array_pop($array);
        }
        return $new_array;
    }

    public function getUser() {
        return $this->user;
    }

    protected function getParentArr($array) {
        // maak arr met indexen
        foreach ($array as $index => $value) {
            $indArr[] = $index;
        }
        $nr = count($indArr);
        if ($nr <= $this->crossOver) {
            $new_array = $array;
        }
        else {
            $selectedInd = array();
            while (count($selectedInd) < $this->crossOver) {
                $i = rand(0, $nr - 1);
                if (!in_array($i, $selectedInd)) {
                    $new_array[$i] = $array[$i];
                    $selectedInd[] = $i;
                }
            }
        }
        return $new_array;
    }

    protected function createChildArr($parent_user_dna, $parent_item_dna) {
        $shuffle_parent_item_dna = $this->shuffle_array(array_values($parent_item_dna));
        $child_item_array_chunks = array_chunk($shuffle_parent_item_dna, round($this->crossOver/2), true);
        // print_r($child_item_array_chunks);
        //dump($parent_user_dna);
        $shuffle_parent_user_dna = $this->shuffle_array(array_values($parent_user_dna));
        //dump($shuffle_parent_user_dna);
        $child_user_array_chunks = array_chunk($shuffle_parent_user_dna, round($this->crossOver/2), true);
        $child_dna = array_merge($child_item_array_chunks[0], $child_user_array_chunks[0]);
        return $child_dna;
    }
}
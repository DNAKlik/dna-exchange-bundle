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

    public function __construct( DnaExchangeUserStampRepository $dnaExchangeUserStampRepository, DnaExchangeContentStampRepository $dnaExchangeContentStampRepository, ManagerRegistry $manager, UsageTrackingTokenStorage $usageTrackingTokenStorage) {

        $this->dnaExchangeUserStampRepository = $dnaExchangeUserStampRepository;
        $this->dnaExchangeContentStampRepository = $dnaExchangeContentStampRepository;
        $this->manager = $manager;
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
        $entityManager = $this->manager->getManager();
        $itemParentStamp = $dnaStampContent->getStamp();
        //dump($userStamps);
        //dump($itemStamps);
        if (count($userStamps) < 1 && count($itemStamps) < 1) {
            // stop 4 moeder stamps van item in item
            for($i = 1;$i <= round($this->crossOver/2); $i++) {
                $dnaExchangeContentStamp = new DnaExchangeContentStamp();
                $dnaExchangeContentStamp->setDnaExchangeContent($dnaStampContent);
                $dnaExchangeContentStamp->setStamp($itemParentStamp);
                $dnaExchangeContentStamp->setCounter($i);
                $dnaStampContent->addDnaExchangeContentStamp($dnaExchangeContentStamp);
                $itemStamps[] = $itemParentStamp;
            }
            $entityManager->persist($dnaStampContent);
            $entityManager->flush();
            // stop 4 moeder stamps van item in user
            for($i = 1;$i <= round($this->crossOver/2); $i++) {
                if ($this->user) {
                    $dnaExchangeUserStamp = new DnaExchangeUserStamp();
                    $dnaExchangeUserStamp->setUser($this->user);
                    if (!is_null($this->user->getSelectedProfile())) {
                        $dnaExchangeUserStamp->setProfile($this->user->getSelectedProfile());
                    }
                    $dnaExchangeUserStamp->setStamp($itemParentStamp);
                    $dnaExchangeUserStamp->setCounter($i);
                    $entityManager->persist($dnaExchangeUserStamp);
                }
                $userStamps[] = $itemParentStamp;
            }
            $entityManager->flush();
            //dump($dnaStampContent);
        }
        elseif (count($userStamps) < 1 ) {
            // echo "geen stamps van user naar item";
            // stop 4 moeder stamps van item in user
            for($i = 1;$i <= round($this->crossOver/2); $i++) {
                if ($this->user) {
                    $dnaExchangeUserStamp = new DnaExchangeUserStamp();
                    $dnaExchangeUserStamp->setUser($this->user);
                    if (!is_null($this->user->getSelectedProfile())) {
                        $dnaExchangeUserStamp->setProfile($this->user->getSelectedProfile());
                    }
                    $dnaExchangeUserStamp->setStamp($itemParentStamp);
                    $dnaExchangeUserStamp->setCounter($i);
                    $entityManager->persist($dnaExchangeUserStamp);
                }
                $userStamps[] = $itemParentStamp;
            }
            //dump($dnaStampContent);
            // stop 4 stamps van item in user
            $parent_item_array = array_chunk($this->shuffle_array($itemStamps), $this->crossOver, true);
            $parent_item_dna = $parent_item_array[0];
            $itemCounter = count($itemStamps);
            for($i = 1;$i <= round($this->crossOver/2); $i++) {
                $insert_stamp = array_shift($parent_item_dna);
                if ($this->user) {
                    $dnaExchangeUserStamp = new DnaExchangeUserStamp();
                    $dnaExchangeUserStamp->setUser($this->user);
                    if (!is_null($this->user->getSelectedProfile())) {
                        $dnaExchangeUserStamp->setProfile($this->user->getSelectedProfile());
                    }
                    $dnaExchangeUserStamp->setStamp($insert_stamp);
                    $dnaExchangeUserStamp->setCounter($itemCounter+$i);
                    $entityManager->persist($dnaExchangeUserStamp);
                }
                $userStamps[] = $insert_stamp;
            }
            if(count($itemStamps ) <= ($this->maxStamps - $this->crossOver/2)) {
                // stop 4 moeder stamps van item in item
                $itemCounter = count($itemStamps);
                for($i = 1;$i <= round($this->crossOver/2); $i++) {
                    $dnaExchangeContentStamp = new DnaExchangeContentStamp();
                    $dnaExchangeContentStamp->setDnaExchangeContent($dnaStampContent);
                    $dnaExchangeContentStamp->setStamp($itemParentStamp);
                    $dnaExchangeContentStamp->setCounter($itemCounter + $i);
                    $dnaStampContent->addDnaExchangeContentStamp($dnaExchangeContentStamp);
                    $itemStamps[] = $itemParentStamp;
                }
            }
            $entityManager->flush();
        }
        elseif (count($itemStamps ) < 1 ) {
            // stop 4 moeder stamps van item in item
            for($i = 1;$i <= round($this->crossOver/2); $i++) {
                $dnaExchangeContentStamp = new DnaExchangeContentStamp();
                $dnaExchangeContentStamp->setDnaExchangeContent($dnaStampContent);
                $dnaExchangeContentStamp->setStamp($itemParentStamp);
                $dnaExchangeContentStamp->setCounter($i);
                $dnaStampContent->addDnaExchangeContentStamp($dnaExchangeContentStamp);
                $itemStamps[] = $itemParentStamp;
            }
            // stop 4 stamps van user in item
            $parent_user_dna = $this->getParentArr($userStamps);
            $parent_user_array = array_chunk($parent_user_dna, $this->crossOver, true);
            dump($parent_user_array);
            $itemCounter = count($itemStamps);
            for($i = 1;$i <= round($this->crossOver/2); $i++) {
                if (count($parent_user_array[0]) > 0) {
                    $insert_stamp = array_shift($parent_user_array[0]);
                    //$sql = "INSERT INTO ".$this->item_stamps_table." SET item_id=".$item_id.", stamp='".$insert_stamp["stamp"]."'";
                    // $this->mysql->bind( "stamp", $insert_stamp["stamp"] );
                    //$ins_stamp = $insert_stamp["stamp"];
                    $dnaExchangeContentStamp = new DnaExchangeContentStamp();
                    $dnaExchangeContentStamp->setDnaExchangeContent($dnaStampContent);
                    $dnaExchangeContentStamp->setStamp($insert_stamp);
                    $dnaExchangeContentStamp->setCounter($itemCounter+$i);
                    $dnaStampContent->addDnaExchangeContentStamp($dnaExchangeContentStamp);
                    $itemStamps[] = $insert_stamp;
                }
                else {
                    // vul aan met moeder stamps
                    //$sql = "INSERT INTO ".$this->item_stamps_table." SET item_id=".$item_id.", stamp='".$stamp."'";
                    // $this->mysql->bind( "stamp", $stamp );
                    $dnaExchangeContentStamp = new DnaExchangeContentStamp();
                    $dnaExchangeContentStamp->setDnaExchangeContent($dnaStampContent);
                    $dnaExchangeContentStamp->setStamp($itemParentStamp);
                    $dnaExchangeContentStamp->setCounter($itemCounter+$i);
                    $dnaStampContent->addDnaExchangeContentStamp($dnaExchangeContentStamp);
                    $itemStamps[] = $itemParentStamp;
                    //$ins_stamp = $stamp;
                }
            }
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
                        $entityManager->persist($dnaExchangeUserStamp);
                    }
                    $userStamps[] = $itemParentStamp;
                }
                else {
                    // stop 4 moeder stamps in user op plek van de user parent stamps
                    $dnaExchangeUserStamp = $this->dnaExchangeUserStampRepository->findOneBy(array("user" => $this->user, "profile" => $this->user->getSelectedProfile(), "Counter" => $parentIndexArr[$counter]+1));
                    if (is_null($dnaExchangeUserStamp)) {
                        dump($parentIndexArr[$counter]);
                    }
                    $dnaExchangeUserStamp->setStamp($itemParentStamp);
                    $entityManager->persist($dnaExchangeUserStamp);
                    $userStamps[$parentIndexArr[$counter]] = $itemParentStamp;
                    $counter++;
                }
            }
            $entityManager->flush();
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
            $child_user_dna = $this->createChildArr($parent_user_dna, $parent_item_dna);

            if ($itemCounter <= ($this->maxStamps - $this->crossOver)) {
                // array content_stamp is nog niet vol, dus child stamps item worden toegevoegd
                $i = 1;
                foreach ($child_item_dna as $stamp) {
                    $dnaExchangeContentStamp = new DnaExchangeContentStamp();
                    $dnaExchangeContentStamp->setDnaExchangeContent($dnaStampContent);
                    $dnaExchangeContentStamp->setStamp($stamp);
                    $dnaExchangeContentStamp->setCounter($itemCounter+$i);
                    $dnaStampContent->addDnaExchangeContentStamp($dnaExchangeContentStamp);
                    $itemStamps[] = $stamp;
                    $i++;
                }
            }
            elseif ($itemCounter < $this->maxStamps) {
                // array user stamps is nog niet vol, dus child stamps user worden toegevoegd
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
                        $entityManager->persist($dnaExchangeContentStamp);
                        $itemStamps[$parentIndexArr[$counter]] = $stamp;
                    }
                    $counter++;
                }
            }
            else {
                foreach($parent_item_dna as $ind => $stamp) {
                    $parentIndexArr[] = $ind;
                }
                $counter = 0;
                foreach ($child_item_dna as $stamp) {
                    $dnaExchangeContentStamp = $this->dnaExchangeContentStampRepository->findOneBy(array("dnaExchangeContent" => $dnaStampContent, "Counter" => $parentIndexArr[$counter]+1));
                    if (is_null($dnaExchangeContentStamp)) {
                        dump($parent_item_dna);
                        dump($parentIndexArr);
                        dump($counter);
                        dump($parentIndexArr[$counter]);
                    }
                    $dnaExchangeContentStamp->setStamp($stamp);
                    $entityManager->persist($dnaExchangeContentStamp);
                    $itemStamps[$parentIndexArr[$counter]] = $stamp;
                    $counter++;
                }
            }
            if ($userCounter <= ($this->maxStamps - $this->crossOver)) {
                // array user stamps is nog niet vol, dus child stamps user worden toegevoegd
                $i = 1;
                foreach ($child_user_dna as $stamp) {
                    if ($this->user) {
                        $dnaExchangeUserStamp = new DnaExchangeUserStamp();
                        $dnaExchangeUserStamp->setUser($this->user);
                        if (!is_null($this->user->getSelectedProfile())) {
                            $dnaExchangeUserStamp->setProfile($this->user->getSelectedProfile());
                        }
                        $dnaExchangeUserStamp->setStamp($stamp);
                        $dnaExchangeUserStamp->setCounter($i+$userCounter);
                        $entityManager->persist($dnaExchangeUserStamp);
                    }
                    $userStamps[] = $stamp;
                    $i++;
                }
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
                            $entityManager->persist($dnaExchangeUserStamp);
                            $userStamps[] = $stamp;
                            $i++;
                        }
                        else {
                            $dnaExchangeUserStamp = $this->dnaExchangeUserStampRepository->findOneBy(array("user" => $this->user, "profile" => $this->user->getSelectedProfile(), "Counter" => $parentIndexArr[$counter]+1));
                            if (is_null($dnaExchangeUserStamp)) {
                                dump($parentIndexArr[$counter]);
                            }
                            $dnaExchangeUserStamp->setStamp($stamp);
                            $entityManager->persist($dnaExchangeUserStamp);
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
                $i = 1;
                foreach($parent_user_dna as $ind => $stamp) {
                    $parentIndexArr[] = $ind;
                }
                $counter = 0;
                foreach ($child_user_dna as $stamp) {
                    if ($this->user) {
                        $dnaExchangeUserStamp = $this->dnaExchangeUserStampRepository->findOneBy(array("user" => $this->user, "profile" => $this->user->getSelectedProfile(), "Counter" => ($parentIndexArr[$counter]+1)));
                        if (is_null($dnaExchangeUserStamp)) {
                            dump($parent_user_dna);
                            dump($parentIndexArr);
                            dump($counter);
                            dump($parentIndexArr[$counter]);
                        }
                        $dnaExchangeUserStamp->setStamp($stamp);
                        $entityManager->persist($dnaExchangeUserStamp);
                    }
                    $userStamps[$parentIndexArr[$counter]] = $stamp;
                    $counter++;
                }
            }
            $entityManager->flush();

        }
        $stampsCollection = array("userStamps" => $userStamps, "itemStamps" => $itemStamps);
        return $stampsCollection;
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
        $child_item_array_chunks = array_chunk($this->shuffle_array(array_values($parent_item_dna)), round($this->crossOver/2), true);
        // print_r($child_item_array_chunks);
        $child_user_array_chunks = array_chunk($this->shuffle_array(array_values($parent_user_dna)), round($this->crossOver/2), true);
        $child_dna = array_merge($child_item_array_chunks[0], $child_user_array_chunks[0]);
        return $child_dna;
    }
}
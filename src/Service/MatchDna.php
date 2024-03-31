<?php

namespace DnaKlik\DnaExchangeBundle\Service;

use DnaKlik\DnaExchangeBundle\Repository\DnaExchangeContentRepository;
use DnaKlik\DnaExchangeBundle\Repository\DnaExchangeContentStampRepository;
use DnaKlik\DnaExchangeBundle\Repository\DnaExchangeUserStampRepository;
use Doctrine\Persistence\ManagerRegistry;

class MatchDna
{
    private $itemStamps = array();

    private $userStamps = array();

    public function __construct(DnaExchangeContentRepository $dnaExchangeContentRepository, DnaExchangeContentStampRepository $dnaExchangeContentStampRepository ,DnaExchangeUserStampRepository $dnaExchangeUserStampRepository, ManagerRegistry $registry)
    {
        $this->dnaExchangeContentRepository = $dnaExchangeContentRepository;
        $this->dnaExchangeContentStampRepository = $dnaExchangeContentStampRepository;
        $this->dnaExchangeUserStampRepository = $dnaExchangeUserStampRepository;
        $this->registry = $registry;
    }

    public function getUserStamps() {
        return $this->userStamps;
    }

    public function getItemStamps() {
        return $this->itemStamps;
    }

    public function findMatchItems($dna, $maxContent) {
        $items = array();
        $dnaCompressed = $this->compressDna($dna);
        //dump($dnaCompressed);
        $matchedContent = array();
        foreach($dnaCompressed as $stamp => $counter) {
            $totalStamp = $this->dnaExchangeContentStampRepository->countTotalStamps($stamp);
            //dump($totalStamp);
            $records = $this->dnaExchangeContentStampRepository->countStampInContent($stamp);
            //dump($records);
            foreach($records as $record) {
                $matchedContent[$record["id"]][$stamp]["stampCount"] = $record["stampCount"];
                $matchedContent[$record["id"]][$stamp]["stampMatchValue"] = $record["stampCount"] * $counter/$totalStamp; // is max 1
            }
        }
        //dump($matchedContent);
        foreach($matchedContent as $id => $stampValues) {
            $totalStampCount = $this->dnaExchangeContentStampRepository->countTotalStampsInContent($id);
            $matchedContentNew[$id]["matchStampsCount"] = 0;
            $matchedContentNew[$id]["match"] = 0;
            $matchedContentNew[$id]["matchCorr"] = 0;
            foreach ($stampValues as $stamp => $values) {
                $matchedContentNew[$id]["matchStamps"][$stamp] = $values["stampCount"];
                $matchedContentNew[$id]["stampMatchValue"][$stamp] = $values["stampMatchValue"];
                $matchedContentNew[$id]["matchStampsCount"] += $values["stampCount"];
                $matchedContentNew[$id]["match"] += $values["stampMatchValue"];
                $matchedContentNew[$id]["matchCorr"] += $values["stampMatchValue"] * 1;
            }
            $matchedContentNew[$id]["totalStampCount"] = $totalStampCount;
        }
        //$sortedArr = array_multisort($matchedContent["matchCorr"], SORT_ASC, SORT_NUMERIC,
        //    $matchedContent["match"], SORT_NUMERIC, SORT_DESC);
        //dump($sortedArr);
        if(isset($matchedContentNew)) {
            uasort($matchedContentNew, fn($a, $b) => $b['matchCorr'] <=> $a['matchCorr']);
            $matchedContentLimit = array_chunk($matchedContentNew, $maxContent, true);
        }
        else {
            $matchedContentLimit = array();
        }
        if (isset($matchedContentLimit[0])) {
            foreach ($matchedContentLimit[0] as $item_id => $values) {
                $items[$item_id] = $values;
                //dump($values);
                $contentObj = $this->dnaExchangeContentRepository->find($item_id);
                $items[$item_id]["slug"] = $contentObj->getSlug();
                $items[$item_id]["stamps"] = array();
                foreach ($contentObj->getDnaExchangeContentStamp() as $stamps) {
                    //dump($stamps);
                    if (isset($items[$item_id]["stamps"][$stamps->getStamp()])) {
                        $items[$item_id]["stamps"][$stamps->getStamp()]++;
                    } else {
                        $items[$item_id]["stamps"][$stamps->getStamp()] = 1;
                    }
                }
            }
        }
        else {
            $items = array();
        }
        //dump($items);
        return $items;

    }
    public function findMatchUsers($dna, $maxContent) {
        $users = array();
        $dnaCompressed = $this->compressDna($dna);
        $matchedUsers = array();
        foreach($dnaCompressed as $stamp => $counter) {
            //dump($stamp);
            $totalStamp = $this->dnaExchangeUserStampRepository->countTotalStamps($stamp); // total stamp occurence in users
            //dump($totalStamp);
            $records = $this->dnaExchangeUserStampRepository->countStampInUser($stamp);  // stamp count per matched user
            foreach($records as $record) {
                if (is_null($record["profileId"])) {
                    $record["profileId"] = 0;
                }
                $matchedUsers[$record["userId"]][$record["profileId"]][$stamp]["stampCount"] = $record["stampCount"];
                $matchedUsers[$record["userId"]][$record["profileId"]][$stamp]["stampMatchValue"] = $record["stampCount"] * $counter/$totalStamp;
            }
        }
        foreach($matchedUsers as $user_id => $userValues) {
            foreach($userValues as $profile_id => $profileValues) {
                //dump($user_id."-".$profile_id);
                //dump($profileValues);
                $totalStampCountUser = $this->dnaExchangeUserStampRepository->countTotalStampsInUserProfile($user_id, $profile_id); // count total stamps in matched users
                //dump($totalStampCount);
                $totalStampCount = $totalStampCountUser["stampCount"];
                $matchedUserProfileNew[$user_id."-".$profile_id]["userId"] = $totalStampCountUser["userId"];
                $matchedUserProfileNew[$user_id."-".$profile_id]["userName"] = $totalStampCountUser["userName"];
                if (isset($totalStampCountUser["profileId"])){
                    $matchedUserProfileNew[$user_id."-".$profile_id]["profileId"] = $totalStampCountUser["profileId"];
                    $matchedUserProfileNew[$user_id."-".$profile_id]["profileName"] = $totalStampCountUser["profileName"];
                }
                $matchedUserProfileNew[$user_id."-".$profile_id]["matchStampsCount"] = 0;
                $matchedUserProfileNew[$user_id."-".$profile_id]["match"] = 0;
                $matchedUserProfileNew[$user_id."-".$profile_id]["matchCorr"] = 0;
                foreach ($profileValues as $stamp => $values) {
                    $matchedUserProfileNew[$user_id."-".$profile_id]["matchStamps"][$stamp] = $values["stampCount"];
                    $matchedUserProfileNew[$user_id."-".$profile_id]["matchStampsCount"] += $values["stampCount"];
                    $matchedUserProfileNew[$user_id."-".$profile_id]["match"] += $values["stampMatchValue"];
                    $matchedUserProfileNew[$user_id."-".$profile_id]["matchCorr"] += $values["stampMatchValue"] * 1;
                }
                $matchedUserProfileNew[$user_id."-".$profile_id]["totalStampCount"] = $totalStampCount;
            }
        }
        //dump($matchedUserProfileNew);
        if (isset($matchedUserProfileNew)) {
            uasort($matchedUserProfileNew, fn($a, $b) => $b['matchCorr'] <=> $a['matchCorr']);
            //dump($matchedUserProfileNew);
            $matchedUserProfileLimit = array_chunk($matchedUserProfileNew, $maxContent, true);
            foreach($matchedUserProfileLimit[0] as $id => $values) {
                $items[$id] = $values;
                //dump($items);
                $ids = explode("-",$id);
                //dump($ids);
                $items[$id]["stamps"] = $this->dnaExchangeUserStampRepository->getStampsFromUser($ids[0], $ids[1]);
            }
            //dump($items);
            return $items;
        }
        else {
            return false;
        }

    }

    private function compressDna($dna) {
        $comprssedDna = array();
        foreach($dna as $ind => $stamp) {
            if (isset($comprssedDna[$stamp])) {
                $comprssedDna[$stamp]++;
            }
            else {
                $comprssedDna[$stamp] = 1;
            }
        }
        return $comprssedDna;
    }
}

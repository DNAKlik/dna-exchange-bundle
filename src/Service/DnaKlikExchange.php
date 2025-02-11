<?php

namespace DnaKlik\DnaExchangeBundle\Service;

use DnaKlik\DnaExchangeBundle\Service\DnaKlikStampProvider;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Generate random "dna exchange" text DnaKlik style!
 *
 * @author Walter Mulder <info@dnaklik.nl>
 */
class DnaKlikExchange
{
    private $crossOver;

    private $maxStamps;

    private $options;

    private $stampProvider;

    private $stamp;

    private $userStamps;

    private $itemStamps;

    private $user = null;

    //private $dnaExchangeContentRepository;

    public function __construct(array $stampProvider = array(), $crossOver = 8, $maxStamps = 64, array $options = array())
    {
        //dump($stampProvider);
        $this->stampProvider = $stampProvider[0];
        $this->crossOver = $crossOver;
        $this->maxStamps = $maxStamps;
        $this->stampProvider->stampsCrossOver->setCrossOver($crossOver);
        $this->stampProvider->stampsCrossOver->setMaxStamps($maxStamps);
        $this->options = $options;
    }

    /**
     * Credit to joshtronic/php-dnaexchange! https://github.com/joshtronic/php-dnaexchange
     *
     * Generates stamps of dna exchange.
     *
     * @access public
     * @param  string $slug of content to get stamp
     * @return string   string of generated dna exchange stamps
     */
    public function getContent() {
        $content = $this->stampProvider->getContent();
        return $content;
    }

    public function getProfiles() {
        $content = $this->stampProvider->getProfiles();
        return $content;
    }

    public function getOptions() {
        return $this->options;
    }

    /**
     * Credit to joshtronic/php-dnaexchange! https://github.com/joshtronic/php-dnaexchange
     *
     * Generates stamps of dna exchange.
     *
     * @access public
     * @param  string $slug of content to get stamp
     * @return string   string of generated dna exchange stamps
     */
    public function getStamp(Request $request) {
        //dump($request->cookies->get("dna"));
        //dump($request->getSession());
        $session = $request->getSession();
        //dump($session->get('dna'));
        $slug = $request->getRequestUri();
        $criteria = array("slug" => $slug);
        //dump($criteria);
        if (null === $this->stamp) {
            $stamp = "0000";

            $stamp = $this->stampProvider->getStamp($criteria);
            $this->user = $this->stampProvider->getUser();
            $userStamps = $this->stampProvider->getUserStamps();

            $this->stamp = $stamp;
        }
        if (!$this->user) {
            $userStamps = $session->get("dna"); // is array
        }
        if (is_null($userStamps)) {
            $userStamps = array();
        }

        $stampCollection = $this->stampProvider->getStampsAfterCrossover($criteria, $userStamps);

        //dump($stampCollection);
        $this->setUserStamps($stampCollection["userStamps"]);
        $this->setItemStamps($stampCollection["itemStamps"]);
        //dump($stampCollection["userStamps"]);
        if (count($stampCollection["userStamps"]) > 0 && !$this->user) {
            // $cookie = $this->setCookie($stampCollection["userStamps"]);
            //dump("set dna");
            $session->set("dna", $stampCollection["userStamps"]);
        }
        //dump($session);
        return $this->stamp;
    }

    /**
     * Credit to joshtronic/php-dnaexchange! https://github.com/joshtronic/php-dnaexchange
     *
     * Deletes stored stamps in session .
     *
     * @access public
     * @param  string $slug of content to get stamp
     * @return string   string of generated dna exchange stamps
     */
    public function deleteSessionDna(Request $request) {
        $session = $request->getSession();
        $dna = $session->get('dna');
        $session->set("dna", array());
        return $dna;
    }

    public function getRelatedContent(Request $request, $maxContent) {
        $session = $request->getSession();
        $session->get('dna');
        $slug = $request->getRequestUri();
        $criteria = array("slug" => $slug);

        $itemStamps = $this->stampProvider->getItemStamps($criteria);
        $userStamps = $this->stampProvider->getUserStamps();
        $this->user = $this->stampProvider->getUser();

        if (!$this->user) {
            $userStamps = $session->get("dna"); // is array
        }
        if (is_null($userStamps)) {
            $userStamps = array();
        }
        //dump($userStamps);
        //dump($itemStamps);
        $this->setUserStamps($userStamps);
        $this->setItemStamps($itemStamps);
        $dna = array_merge($itemStamps, $userStamps);
        $items = $this->stampProvider->findMatchItems($dna, $maxContent);
        //$users = $stampProvider->findMatchUsers($dna);
        return $items;
    }

    public function getRelatedContentFromContent($slug, $maxContent) {
        $criteria = array("slug" => $slug);

        $itemStamps = $this->stampProvider->getItemStamps($criteria);
        $this->setItemStamps($itemStamps);

        $items = $this->stampProvider->findMatchItems($itemStamps, $maxContent);
        //$users = $stampProvider->findMatchUsers($dna);
        return $items;
    }

    public function getMatchedContentForDna($dna, $maxContent) {
        $items = $this->stampProvider->findMatchItems($dna, $maxContent);
        return $items;
    }

    public function getMatchedUserForDna($dna, $maxContent) {
        $items = $this->stampProvider->findMatchUsers($dna, $maxContent);
        return $items;
    }

    public function getUserStamps() {
        return $this->userStamps;
    }

    public function getItemStamps() {
        return $this->itemStamps;
    }

    public function getItemStampsFromContent($id) {
        $this->itemStamps = $this->stampProvider->getItemStamps(array("id" => $id));
        return $this->itemStamps;
    }

    public function getContentFromId($contentId) {
        $content = $this->stampProvider->getContentFromId($contentId);
        return $content;
    }

    public function getContentFromSlug($slug) {
        $content = $this->stampProvider->getContentFromSlug($slug);
        return $content;
    }

    public function getUserStampsFromUserProfile($user_id, $profile_id) {
        $this->itemStamps = $this->stampProvider->getUserProfileStamps($user_id, $profile_id);
        return $this->itemStamps;
    }

    public function getUserContent($user_id, $profile_id) {
        $userContent = $this->stampProvider->getUserContent($user_id, $profile_id);
        return $userContent;
    }

    public function getUserStampsFromContent($id) {
        $this->itemStamps = $this->stampProvider->getUserStamps(array("id" => $id));
        return $this->itemStamps;
    }

    public function setUserStamps($userStamps) {
        $this->userStamps = $userStamps;
    }

    public function setItemStamps($itemStamps) {
        $this->itemStamps = $itemStamps;
    }

    public function getUser() {
        return  $this->user;
    }

    public function compressDna($dna) {
        $comprssedDna = array();
        foreach($dna as $ind => $stampValues) {
            if (isset($comprssedDna[$stampValues["Stamp"]])) {
                $comprssedDna[$stampValues["Stamp"]]++;
            }
            else {
                $comprssedDna[$stampValues["Stamp"]] = 1;
            }
        }
        return $comprssedDna;
    }
}

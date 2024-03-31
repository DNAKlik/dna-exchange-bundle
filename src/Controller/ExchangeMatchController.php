<?php

namespace DnaKlik\DnaExchangeBundle\Controller;

use DnaKlik\DnaExchangeBundle\Event\FilterApiResponseEvent;
use DnaKlik\DnaExchangeBundle\Event\DnaKlikDnaExchangeEvents;
use DnaKlik\DnaExchangeBundle\Service\DnaKlikExchange;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\FileLocatorInterface;
use Twig\Loader\FilesystemLoader;
use Symfony\Component\Routing\Annotation\Route;

#[Route('exchange/admin/match')]
class ExchangeMatchController extends AbstractController
{
    private $dnaKlikExchange;

    private $eventDispatcher;

    private $filesystemLoader;

    private $templateLocator;

    private $pageLimit = 20;

    public function __construct(DnaKlikExchange $dnaKlikExchange, FilesystemLoader $filesystemLoader, EventDispatcherInterface $eventDispatcher = null)
    {
        $this->dnaKlikExchange = $dnaKlikExchange;
        $this->eventDispatcher = $eventDispatcher;
        $filesystemLoader->addPath('../dna-exchange-bundle/templates/', $namespace = '__main__');
    }



    #[Route('/cc/{id}', name: 'match_content_content', methods:['GET','POST'])]
    public function matchContentWithContent(Request $request)
    {
        $content_id = $request->get("id");
        $dna = $this->dnaKlikExchange->getItemStampsFromContent($content_id);
        $content = $this->dnaKlikExchange->getContentFromId($content_id);
        $items = $this->dnaKlikExchange->getMatchedContentForDna($dna, $this->pageLimit);
        // dump($items);
        foreach($items as $ind => $item) {
            $dnaStamps = "";
            foreach($item["stamps"] as $stamp => $stampCount) {
                if (isset($item["matchStamps"][$stamp])) {
                    $dnaStamps .= "<span class='match'>".$stamp." (".$stampCount.")</span> ";
                }
                else {
                    $dnaStamps .= $stamp." (".$stampCount.") ";
                }
            }
            $items[$ind]["dnaStamps"] = $dnaStamps;
        }


        $data = [
            'dna' => $dna,
            'items' => $items,
            'content' => $content
        ];

        return $this->render('dna-admin/matchcontent.html.twig', [
            'data' => $data,
            'options' => $this->dnaKlikExchange->getOptions()
        ]);
    }

    #[Route('/cu/{id}', name: 'match_content_user', methods:['GET','POST'])]
    public function matchContentWithUser(Request $request)
    {
        $content_id = $request->get("id");
        $dna = $this->dnaKlikExchange->getItemStampsFromContent($content_id);
        $items = $this->dnaKlikExchange->getMatchedUserForDna($dna, $this->pageLimit);
        foreach($items as $ind => $item) {
            $dnaStamps = "";
            $dna = $this->dnaKlikExchange->compressDna($item["stamps"]);
            foreach($dna as $stamp => $stampCount) {
                if (isset($item["matchStamps"][$stamp])) {
                    $dnaStamps .= "<span class='match'>".$stamp." (".$stampCount.")</span> ";
                }
                else {
                    $dnaStamps .= $stamp." (".$stampCount.") ";
                }
            }
            $items[$ind]["dnaStamps"] = $dnaStamps;
        }


        $data = [
            'dna' => $dna,
            'items' => $items
        ];

        return $this->render('dna-admin/matchuser.html.twig', [
            'data' => $data,
            'options' => $this->dnaKlikExchange->getOptions()
        ]);
    }

    #[Route('/uc/{user_id}/{profile_id}', name: 'match_user_content', methods:['GET','POST'])]
    public function matchUserWithContent(Request $request) {
        $user_id = $request->get("user_id");
        $profile_id = $request->get("profile_id");
        $dna = $this->dnaKlikExchange->getUserStampsFromUserProfile($user_id, $profile_id);
        $items = $this->dnaKlikExchange->getMatchedContentForDna($dna, $this->pageLimit);
        foreach($items as $ind => $item) {
            $dnaStamps = "";
            foreach($item["stamps"] as $stamp => $stampCount) {
                if (isset($item["matchStamps"][$stamp])) {
                    $dnaStamps .= "<span class='match'>".$stamp." (".$stampCount.")</span> ";
                }
                else {
                    $dnaStamps .= $stamp." (".$stampCount.") ";
                }
            }
            $items[$ind]["dnaStamps"] = $dnaStamps;
        }

        $data = [
            'dna' => $dna,
            'items' => $items
        ];

        return $this->render('dna-admin/matchcontent.html.twig', [
            'data' => $data,
            'options' => $this->dnaKlikExchange->getOptions()
        ]);
    }

    #[Route('/uu/{user_id}/{profile_id}', name: 'match_user_user', methods:['GET','POST'])]
    public function matchUserWithUser(Request $request) {
        $user_id = $request->get("user_id");
        $profile_id = $request->get("profile_id");
        $dna = $this->dnaKlikExchange->getUserStampsFromUserProfile($user_id, $profile_id);
        $items = $this->dnaKlikExchange->getMatchedUserForDna($dna, $this->pageLimit);
        foreach($items as $ind => $item) {
            $dnaStamps = "";
            $dna = $this->dnaKlikExchange->compressDna($item["stamps"]);
            foreach($dna as $stamp => $stampCount) {
                if (isset($item["matchStamps"][$stamp])) {
                    $dnaStamps .= "<span class='match'>".$stamp." (".$stampCount.")</span> ";
                }
                else {
                    $dnaStamps .= $stamp." (".$stampCount.") ";
                }
            }
            $items[$ind]["dnaStamps"] = $dnaStamps;
        }

        $data = [
            'dna' => $dna,
            'items' => $items
        ];

        return $this->render('dna-admin/matchuser.html.twig', [
            'data' => $data,
            'options' => $this->dnaKlikExchange->getOptions()
        ]);
    }
}
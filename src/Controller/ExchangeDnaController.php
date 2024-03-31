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

#[Route('/exchange/admin')]
class ExchangeDnaController extends AbstractController
{
    private $dnaKlikExchange;

    private $eventDispatcher;

    private $filesystemLoader;

    private $templateLocator;

    public function __construct(DnaKlikExchange $dnaKlikExchange, FilesystemLoader $filesystemLoader, EventDispatcherInterface $eventDispatcher = null)
    {
        $this->dnaKlikExchange = $dnaKlikExchange;
        $this->eventDispatcher = $eventDispatcher;
        $filesystemLoader->addPath('../dna-exchange-bundle/templates/', $namespace = '__main__');
    }

    #[Route('', name: 'index')]
    public function __invoke(Request $request)
    {
        $uriParts = explode("/", $request->getRequestUri());
        dump($request);
        dump($uriParts);
        if (isset($uriParts[3])) {
            if ($uriParts[3] == "profiles") {
                return $this->getProfiles();
            }
        }
        return $this->index();
    }

    #[Route('/', name: 'dna_home')]
    public function index()
    {
        $data = [
            'content' => $this->dnaKlikExchange->getContent(),
            'options' => $this->dnaKlikExchange->getOptions()
        ];
        //dump($data);

        return $this->render('dna-admin/index.html.twig', [
            'content' => $data["content"],
            'options' => $data["options"]
        ]);
    }

    #[Route('/profiles', name: 'dna_profiles')]
    public function getProfiles()
    {
        $data = [
            'content' => $this->dnaKlikExchange->getProfiles(),
            'options' => $this->dnaKlikExchange->getOptions()
        ];

        //dump($data);

        return $this->render('dna-admin/profiles.html.twig', [
            'content' => $data["content"],
            'options' => $data["options"]
        ]);
    }
}
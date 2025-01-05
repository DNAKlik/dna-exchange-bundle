<?php

namespace DnaKlik\DnaExchangeBundle\Controller;

use DnaKlik\DnaExchangeBundle\Service\DnaKlikExchange;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Twig\Loader\FilesystemLoader;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/exchange/admin')]
class ExchangeDnaController extends AbstractController
{
    private $dnaKlikExchange;

    private $eventDispatcher;

    private $doctrine;

    public function __construct(DnaKlikExchange $dnaKlikExchange, FilesystemLoader $filesystemLoader, ManagerRegistry $doctrine, EventDispatcherInterface $eventDispatcher = null)
    {
        $this->dnaKlikExchange = $dnaKlikExchange;
        $this->eventDispatcher = $eventDispatcher;
        $this->doctrine = $doctrine;
        //dump($filesystemLoader);
        $ext_path = $filesystemLoader->getPaths("DnaKlikDnaExchange"); //
        //dump($ext_path);
        $filesystemLoader->addPath($ext_path[0].'/templates/', $namespace = '__main__');
    }

    #[Route('', name: 'index')]
    public function __invoke(Request $request)
    {
        $uriParts = explode("/", $request->getRequestUri());
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

    #[Route('/install', name: 'dna_install')]
    public function install()
    {
        $data = [
            'options' => $this->dnaKlikExchange->getOptions()
        ];

        return $this->render('dna-admin/install.html.twig', [
            'options' => $data["options"]
        ]);
    }

    #[Route('/install_db', name: 'dna_install_db')]
    public function installDb()
    {
        $schemaManager = $this->doctrine->getConnection()->getSchemaManager();
        // dump($schemaManager);
        $nameSpace = 'DnaKlik\DnaExchangeBundle\Entity';
        $tables = array('dna_exchange_content' => 'DnaExchangeContent', 'dna_exchange_content_stamp' => 'DnaExchangeContentStamp', 'dna_exchange_user_stamp' => 'DnaExchangeUserStamp');
        foreach($tables as $table => $entityName) {
            if ($schemaManager->tablesExist(array($table)) == true) {
                $content[] =  "table ".$table." exists";
            } else {
                $manager = $this->doctrine->getManager();
                $metadata = $manager->getClassMetadata($nameSpace.'\\'.$entityName);
                $metadata->setPrimaryTable(array('name' => $table));
                $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($manager);
                $schemaTool->createSchema(array($metadata));
                $content[] =  "table ".$table." created";
            }
        }
        $data = [
            'content' => $content,
            'options' => $this->dnaKlikExchange->getOptions()
        ];

        return $this->render('dna-admin/install.html.twig', [
            'content' => $data["content"],
            'options' => $data["options"]
        ]);
    }
}
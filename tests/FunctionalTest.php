<?php
namespace DnaKlik\DnaExchangeBundle\Tests;

use DnaKlik\DnaExchangeBundle\DnaKlikDnaExchangeBundle;
use DnaKlik\DnaExchangeBundle\Kernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DnaKlik\DnaExchangeBundle\Entity\DnaExchangeContent;

class FunctionalTest extends TestCase
{
    public function testServiceWiring()
    {
        $kernel = new DnaKlikExchangeTestingKernel();
        $kernel->boot();
        $container = $kernel->getContainer();

        /*
        $dnaExchangeContent = new DnaExchangeContent();
        $dnaExchangeContent->setSlug('Test');
        $dnaExchangeContent->setStamp('0000');

        $dnaExchangeContentRepository = $this->createMock(EntityRepository::class);

        $dnaExchangeContentRepository->expects($this->any())
            ->method('find')
            ->willReturn($dnaExchangeContent);
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($dnaExchangeContentRepository);
        */

        $ipsum = $container->get('dnaklik_dna_exchange.dnaklik_content_repository');
        //$this->assertInstanceOf(DnaKlikExchange::class, $ipsum);
        //$this->assertInternalType('string', $ipsum->getOptions());
    }
}

class DnaKlikExchangeTestingKernel extends Kernel
{
    public function __construct()
    {
        parent::__construct('test', true);
    }

    public function registerBundles(): iterable
    {
        return [
            new DnaKlikDnaExchangeBundle(),
        ];
    }
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function(ContainerBuilder $container) {
            $container->register('twig.loader', \Twig\Loader\FilesystemLoader::class);
            $container->register('security.token_storage', \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface::Class);
            $container->register('doctrine', \Doctrine\Persistence\ManagerRegistry::class);
        });
    }
}
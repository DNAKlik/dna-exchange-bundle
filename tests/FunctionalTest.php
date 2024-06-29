<?php
namespace DnaKlik\DnaExchangeBundle\Tests;

use DnaKlik\DnaExchangeBundle\DnaKlikDnaExchangeBundle;
use DnaKlik\DnaExchangeBundle\Kernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class FunctionalTest extends TestCase
{
    public function testServiceWiring()
    {
        $kernel = new DnaKlikExchangeTestingKernel();
        $kernel->boot();
        $container = $kernel->getContainer();

        // dump($container);
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
<?php

namespace DnaKlik\DnaExchangeBundle\Tests\Controller;

use DnaKlik\DnaExchangeBundle\DnaKlikDnaExchangeBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class ExchangeDnaControllerTest extends TestCase
{
    public function testIndex()
    {
        $kernel = new DnaKlikDnaExchangeControllerKernel();
        $client = new KernelBrowser($kernel);
        $client->request('GET', '/dna/');

        $this->assertSame(500, $client->getResponse()->getStatusCode());
    }
}

class DnaKlikDnaExchangeControllerKernel extends Kernel
{
    use MicroKernelTrait;

    public function __construct()
    {
        parent::__construct('test', true);
    }

    public function registerBundles(): iterable
    {
        return [
            new DnaKlikDnaExchangeBundle(),
            new FrameworkBundle(),
        ];
    }

    protected function configureRoutes(RoutingConfigurator $routes)
    {
        $routes->import(__DIR__.'/../../src/Resources/config/routes.xml', '/dna');
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $c->loadFromExtension('framework', [
            'secret' => 'F00',
            'router' => ['utf8' => true],
        ]);
    }

    public function getCacheDir(): string
    {
        return __DIR__.'/../cache/'.spl_object_hash($this);
    }
}
<?php

namespace DnaKlik\DnaExchangeBundle\Tests\Controller;

use DnaKlik\DnaExchangeBundle\DnaKlikDnaExchangeBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
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
            new TwigBundle(),
            new DoctrineBundle(),
            new SecurityBundle()
        ];
    }

    protected function configureRoutes(RoutingConfigurator $routes)
    {
        $routes->import(__DIR__.'/../../src/Resources/config/routes.xml', '/dna');
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
    {
        $c->loadFromExtension('framework', [
            'secret' => 'F00',
            'router' => ['utf8' => true],
        ]);
        $c->loadFromExtension('twig', [
            'default_path' => '%kernel.project_dir%/templates',
            'strict_variables' => false
        ]);
        $c->loadFromExtension('doctrine', [
            'dbal' => [
                'url'=> '%env(resolve:DATABASE_URL)%'
            ],
            'orm' => [
                'auto_generate_proxy_classes' => true,
                'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
                'auto_mapping' => true
            ]
        ]);
        $c->loadFromExtension('security', [
            'providers' => [
                'users_in_memory' => [
                    'memory'=> null
                ]
            ],
            'firewalls' => [
                'dev' => [
                    'pattern' => '^/(_(profiler|wdt)|css|images|js)/',
                    'security' => false
                ],
                'main' => [
                    'lazy' => true,
                    'provider' => 'users_in_memory'
                ]
            ]
        ]);
    }

    public function getCacheDir(): string
    {
        return __DIR__.'/../cache/'.spl_object_hash($this);
    }
}
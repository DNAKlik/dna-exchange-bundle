<?php
namespace DnaKlik\DnaExchangeBundle\Tests;

use DnaKlik\DnaExchangeBundle\DnaKlikDnaExchangeBundle;
use PHPUnit\Framework\TestCase;
use DnaKlik\DnaExchangeBundle\Kernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Reference;

class FunctionalTest extends TestCase
{
    public function testServiceWiring()
    {
        $kernel = new DnaKlikExchangeTestingKernel('test', true);
        $kernel->boot();
        // $container = $kernel->getContainer();
    }
}

class DnaKlikExchangeTestingKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return [
            new DnaKlikDnaExchangeBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
    }
}
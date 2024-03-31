<?php

namespace DnaKlik\DnaExchangeBundle\Tests;

use DnaKlik\DnaExchangeBundle\DnaKlikDnaExchangeBundle;
use DnaKlik\DnaExchangeBundle\Service\DnaKlikExchange;
use DnaKlik\DnaExchangeBundle\Service\StampProviderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class FunctionalTest extends TestCase
{
    public function testServiceWiring()
    {
        $kernel = new DnaKlikDnaExchangeTestingKernel();
        $kernel->boot();
        $container = $kernel->getContainer();

        $exchange = $container->get('dnaklik_dna_exchange.dnaklik_exchange');
        $this->assertInstanceOf(DnaKlikExchange::class, $exchange);
        $this->assertIsString($exchange->getParagraphs());
    }
}

class DnaKlikDnaExchangeTestingKernel extends Kernel
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
            $container->register('stub_stamp_list', StubStampList::class)
                ->addTag('dnaklik_exchange_stamp_provider');
        });
    }

    public function getCacheDir(): string
    {
        return __DIR__.'/cache/'.spl_object_hash($this);
    }
}

class StubStampList implements StampProviderInterface
{
    public function getStampList(): array
    {
        return ['stub', 'stub2'];
    }
}
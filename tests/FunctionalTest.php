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
        $this->assertIsString($exchange->getOptions());
    }
}

class DnaKlikDnaExchangeTestingKernel extends Kernel
{
    private $dnaKlikDnaExchangeConfig;

    public function __construct(array $dnaKlikDnaExchangeConfig = [])
    {
        $this->dnaKlikDnaExchangeConfig = $dnaKlikDnaExchangeConfig;
        parent::__construct('test', true);
    }

    public function registerBundles(): iterable
    {
        return [
            new DnaKlikDnaExchangeBundle()
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function(ContainerBuilder $container) {
            $container->loadFromExtension('dnaklik_dna_exchange', $this->dnaKlikDnaExchangeConfig);
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
    public function getStamp($criteria): string
    {
        return 'stub';
    }

    public function getItemStamps($criteria): array
    {
        return ['stub', 'stub2'];
    }

    public function getStampsAfterCrossover($criteria, $userStamps): array
    {
        return ['stub', 'stub2'];
    }
}
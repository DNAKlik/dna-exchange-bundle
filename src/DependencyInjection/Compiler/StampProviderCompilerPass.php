<?php

namespace DnaKlik\DnaExchangeBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class StampProviderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition('dnaklik_dna_exchange.dnaklik_exchange');
        $references = array();
        foreach ($container->findTaggedServiceIds('dnaklik_exchange_stamp_provider') as $id => $tags) {
            $references[] = new Reference($id);
        }

        $definition->setArgument(0, $references);
    }
}
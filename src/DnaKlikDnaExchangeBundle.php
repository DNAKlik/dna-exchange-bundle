<?php

namespace DnaKlik\DnaExchangeBundle;

use DnaKlik\DnaExchangeBundle\DependencyInjection\Compiler\StampProviderCompilerPass;
use DnaKlik\DnaExchangeBundle\DependencyInjection\DnaKlikDnaExchangeExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DnaKlikDnaExchangeBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new StampProviderCompilerPass());
    }

    public function getContainerExtension(): ?DnaKlikDnaExchangeExtension
    {
        if (null === $this->extension) {
            $this->extension = new DnaKlikDnaExchangeExtension();
        }
        return $this->extension;
    }
}

<?php

namespace DnaKlik\DnaExchangeBundle;

use DnaKlik\DnaExchangeBundle\DependencyInjection\Compiler\StampProviderCompilerPass;
use DnaKlik\DnaExchangeBundle\DependencyInjection\DnaKlikDnaExchangeExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\AssetMapper\AssetMapperInterface;

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

    public function prependExtension(ContainerConfigurator $configurator, ContainerBuilder $container): void
    {
        if (!$this->isAssetMapperAvailable($container)) {
            return;
        }

        $container->prependExtensionConfig('framework', [
            'asset_mapper' => [
                'paths' => [
                    __DIR__ . '/../assets/dist' => '@dnaklik/dna_exchange',
                ],
            ],
        ]);
    }

    private function isAssetMapperAvailable(ContainerBuilder $container): bool
    {
        if (!interface_exists(AssetMapperInterface::class)) {
            return false;
        }

        // check that FrameworkBundle 6.3 or higher is installed
        $bundlesMetadata = $container->getParameter('kernel.bundles_metadata');
        if (!isset($bundlesMetadata['FrameworkBundle'])) {
            return false;
        }

        return is_file($bundlesMetadata['FrameworkBundle']['path'] . '/Resources/config/asset_mapper.php');
    }
}

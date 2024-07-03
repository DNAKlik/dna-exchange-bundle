<?php

namespace DnaKlik\DnaExchangeBundle\DependencyInjection;

use DnaKlik\DnaExchangeBundle\Service\StampProviderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class DnaKlikDnaExchangeExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        $config['assets']['stylesheets'][] = sprintf(
            'bundles/dnaklikdnaexchange/build/%s.css',
            'theme'
        );
        $javascript = $this->buildJavascripts($config);
        $stylesheet = $this->buildStylesheets($config);
        $config['options']['javascripts'] = $javascript;
        $config['options']['stylesheets'] = $stylesheet;

        $definition = $container->getDefinition('dnaklik_dna_exchange.dnaklik_exchange');
        $definition->setArgument(1, $config['crossOver']);
        $definition->setArgument(2, $config['maxStamps']);
        $definition->setArgument(3, $config['options']);
        //dump($definition);

        $container->registerForAutoconfiguration(StampProviderInterface::class)
            ->addTag('dnaklik_exchange_stamp_provider');
    }

    public function getAlias(): string
    {
        return 'dnaklik_dna_exchange';
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return string[]
     */
    private function buildStylesheets(array $config): array
    {
        return $this->mergeArray(
            $config['assets']['stylesheets'],
            $config['assets']['extra_stylesheets'],
            $config['assets']['remove_stylesheets']
        );
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return string[]
     */
    private function buildJavascripts(array $config): array
    {
        return $this->mergeArray(
            $config['assets']['javascripts'],
            $config['assets']['extra_javascripts'],
            $config['assets']['remove_javascripts']
        );
    }

    /**
     * @param array<int, string> $array
     * @param array<int, string> $addArray
     * @param array<int, string> $removeArray
     *
     * @return array<int, string>
     */
    private function mergeArray(array $array, array $addArray, array $removeArray = []): array
    {
        foreach ($addArray as $toAdd) {
            $array[] = $toAdd;
        }
        foreach ($removeArray as $toRemove) {
            $key = array_search($toRemove, $array, true);
            if (false !== $key) {
                array_splice($array, $key, 1);
            }
        }

        return $array;
    }

}

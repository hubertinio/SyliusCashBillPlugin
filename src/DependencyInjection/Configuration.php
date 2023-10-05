<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress UnusedVariable
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('hubertinio_sylius_cash_bill');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode->children()
            ->scalarNode('app_id')->defaultNull()->isRequired()->end()
            ->scalarNode('app_secret')->defaultNull()->isRequired()->end()
            ->scalarNode('api_host')->defaultValue('https://pay.cashbill.pl/ws/rest/')->end();

        return $treeBuilder;
    }
}

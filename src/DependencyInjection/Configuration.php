<?php
/**
 * Created by PhpStorm.
 * User: jvaldena
 * Date: 22/01/2019
 * Time: 16:27
 */

namespace WebEtDesign\AnalyticsBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\HttpFoundation\Request;
use WebEtDesign\CmsBundle\Entity\CmsGlobalVarsDelimiterEnum;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('wd_admin_analytics');

        $rootNode
            ->children()
                ->arrayNode('parameters')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('view_id')->defaultValue(null)->end()
                        ->scalarNode('map_key')->defaultValue(null)->end()
                    ->end()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}

<?php

namespace WebEtDesign\AnalyticsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use WebEtDesign\AnalyticsBundle\Admin\BlockAdmin;
use WebEtDesign\AnalyticsBundle\Entity\Block;

class WDAdminAnalyticsExtension extends Extension
{
    public function load(array $configs, \Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $processor     = new Processor();
        $config        = $processor->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
        $loader->load('admin.yaml');

        $container->setParameter('wd_admin_analytics.view_ids', $config['parameters']['view_ids']);
        $container->setParameter('wd_admin_analytics.view_names', $config['parameters']['view_names']);
        $container->setParameter('wd_admin_analytics.map_key', $config['parameters']['map_key']);

        $container->setParameter('wd_admin_analytics.entity.block', Block::class);

        $container->setParameter('wd_admin_analytics.admin.block', BlockAdmin::class);


    }

    public function getAlias()
    {
        return 'wd_admin_analytics';
    }

}

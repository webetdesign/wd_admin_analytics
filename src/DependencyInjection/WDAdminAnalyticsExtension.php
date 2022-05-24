<?php

namespace WebEtDesign\AnalyticsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use WebEtDesign\AnalyticsBundle\Admin\BlockAdmin;
use WebEtDesign\AnalyticsBundle\Admin\ConfigAdmin;
use WebEtDesign\AnalyticsBundle\Entity\Block;
use WebEtDesign\AnalyticsBundle\Entity\Config;

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
        $container->setParameter('wd_admin_analytics.entity.config', Config::class);

        $container->setParameter('wd_admin_analytics.admin.block', BlockAdmin::class);
        $container->setParameter('wd_admin_analytics.admin.configuration', ConfigAdmin::class);

        $container->setParameter('wd_admin_analytics.google_analytics_json_key', $config['parameters']['google_analytics_json_key']);

    }

    public function getAlias(): string
    {
        return 'wd_admin_analytics';
    }

}

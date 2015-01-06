<?php

namespace Vivait\ReportingBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class VivaitReportingExtension extends Extension implements PrependExtensionInterface
{
    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        //Set user target entities
        $this->prependConfig('doctrine', $container, [
            'orm' => [
                'resolve_target_entities' => [
                    $container->getParameter('vivait_reporting.reporting_user_interface.class') => $config['user_class']
                ]
            ]
        ]);

        //Set Assetic bundles
        $this->prependConfig('assetic', $container, [
            'bundles' => ['VivaitReportingBundle']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
    }

    /**
     * @param $configNode
     * @param ContainerBuilder $container
     * @param $config
     */
    private function prependConfig($configNode, ContainerBuilder $container, $config)
    {
        foreach ($container->getExtensions() as $name => $extension) {
            switch ($name) {
                case $configNode:
                    $container->prependExtensionConfig($name, $config);
                    break;
            }
        }
    }

}

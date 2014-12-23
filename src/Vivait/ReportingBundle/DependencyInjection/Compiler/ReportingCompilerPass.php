<?php

namespace Vivait\ReportingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ReportingCompilerPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('vivait_reporting')) {
            return;
        }

        $definition = $container->getDefinition('vivait_reporting');

        foreach ($container->findTaggedServiceIds('vivait_reporting.report') as $id => $attributes) {
            $definition->addMethodCall('addReport', array($id, new Reference($id)));
        }
    }

}
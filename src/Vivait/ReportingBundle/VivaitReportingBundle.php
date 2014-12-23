<?php

namespace Vivait\ReportingBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Vivait\ReportingBundle\DependencyInjection\Compiler\ReportingCompilerPass;

class VivaitReportingBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ReportingCompilerPass());
    }
}

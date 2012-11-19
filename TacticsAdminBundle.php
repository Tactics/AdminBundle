<?php

namespace Tactics\Bundle\AdminBundle;

use Tactics\Bundle\AdminBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TacticsAdminBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        
        $container->addCompilerPass(new OverrideServiceCompilerPass());
    }   
}

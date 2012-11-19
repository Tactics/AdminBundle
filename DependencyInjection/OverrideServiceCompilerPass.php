<?php

namespace Tactics\Bundle\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('vich_uploader.listener.uploader');
        $definition->setClass('Tactics\Bundle\AdminBundle\Listener\UploaderListener');
    }
}


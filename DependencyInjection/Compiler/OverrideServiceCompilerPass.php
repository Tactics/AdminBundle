<?php

namespace Tactics\Bundle\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('vich_uploader.listener.uploader')
            ->setClass('Tactics\Bundle\AdminBundle\Listener\UploaderListener');
        $container->getDefinition('vich_uploader.adapter')
            ->setClass('Tactics\Bundle\AdminBundle\Adapter\ORM\DoctrineORMAdapter');
        $container->getDefinition('vich_uploader.storage.file_system')
            ->setClass('Tactics\Bundle\AdminBundle\Storage\FileSystemStorage');
    }
}


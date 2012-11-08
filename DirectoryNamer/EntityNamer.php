<?php

namespace Tactics\Bundle\AdminBundle\DirectoryNamer;

use Vich\UploaderBundle\Naming\DirectoryNamerInterface;

class EntityNamer implements DirectoryNamerInterface
{
    /**
     * {@inheritdoc}
     */
    public function directoryName($obj, $field, $uploadDir)
    {
        return $uploadDir . DIRECTORY_SEPARATOR . $obj->getId();
    }
}

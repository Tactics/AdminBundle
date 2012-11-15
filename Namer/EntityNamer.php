<?php

namespace Tactics\Bundle\AdminBundle\Namer;

use Vich\UploaderBundle\Naming\NamerInterface;

class EntityNamer implements NamerInterface
{
    /**
     * {@inheritdoc}
     */
    public function name($obj, $field)
    {
        $getter = 'get'.ucfirst($field);
        $file = $obj->$getter();

        return sha1(uniqid(mt_rand(), true)).'.'.$file->guessExtension();
    }
}

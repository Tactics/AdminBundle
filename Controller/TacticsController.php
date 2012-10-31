<?php

namespace Tactics\Bundle\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TacticsController extends Controller
{
    /**
     *  Creates an not found exception if the objects does not exists
     *
     * @param Object
     * @param (optional) String $type of the object
     */
    public function createExceptionIfNotFound($object, $type = false)
    {
        if (! $object) {
            $notice = ($type ? $type : 'Object') . ' not found.';
            throw $this->createNotFoundException($notice);
        }
    }

}

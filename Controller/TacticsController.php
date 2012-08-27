<?php

namespace Tactics\Bundle\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class TacticsController extends Controller
{
    /**
     *  Creates an not found exception if the objects does not exists
     * 
     * @param Object
     * @param (optional) String $name of object
     */
    public function createExceptionIfNotFound($object, $name = false)
    {     
      if(! $object) {
       $notice = ($name ? $name : 'Object') . ' not found.';
       throw $this->createNotFoundException($notice);
      }
    }
    
}

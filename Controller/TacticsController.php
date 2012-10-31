<?php

namespace Tactics\Bundle\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\QueryBuilder;
use Tactics\Bundle\AdminBundle\QueryBuilderFilter\QueryBuilderPager;

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

    /**
     * @todo I'm not sure whether or not I like this yet.
     * This makes the AdminBundle and TableBundle not so loosely coupled. 
     * Then again, I think AdminBundle and TableBundle will always be used 
     * together.
     *
     * @return $pager Pagerfanta\Pagerfanta A Pagerfanta instance.
     */
    public function getPager($qb)
    {
        $qbp = new QueryBuilderPager($this->container);
        return $qbp->execute($qb);
    }
}

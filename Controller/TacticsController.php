<?php

namespace Tactics\Bundle\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\QueryBuilder;
use Tactics\TableBundle\QueryBuilderFilter\QueryBuilderPager;
use Tactics\TableBundle\QueryBuilderFilter\QueryBuilderSorter;

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
    public function getPager(QueryBuilder $qb, $key = null, $maxPerPage = null)
    {
        $options = array();

        if ($maxPerPage) {
            $options['max_per_page'] = $maxPerPage;
        }

        $qbp = new QueryBuilderPager($this->container);

        return $qbp->execute($qb, $key, $options);
    }

    /**
     * @return QueryBuilder QueryBuilder instance with added orderByClause.
     */
    public function sortQuery(QueryBuilder $qb, $key = null, $options = array())
    {
        $sorter = new QueryBuilderSorter($this->container);

        return $sorter->execute($qb, $key, $options);
    }
}

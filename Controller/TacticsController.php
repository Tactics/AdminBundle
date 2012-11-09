<?php

namespace Tactics\Bundle\AdminBundle\Controller;

use Tactics\TableBundle\QueryBuilderFilter\QueryBuilderFilter;

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

    /**
     * Creates and returns a table builder instance
     *
     * @param mixed $data    The initial data for the form
     * @param array $options Options for the form
     *
     * @return FormBuilder
     */
    public function createTableBuilder($type, array $options = array())
    {
        return $this->container->get('tactics.table.factory')->createBuilder($type, $options);
    }
    
    /**
     * Creates and returns a QueryBuilderFilter instance
     *
     * @param QueryBuilderFilterTypeInterface  $filterType
     *
     * @return QueryBuilderFilter
     */
    public function createFilter($type, array $options = array())
    {
        $filter = new QueryBuilderFilter($this->container);
        $filter->buildFromType($type);
        
        return $filter;
    }

    /**
     * Creates or retrieves an entity.
     *
     * @param Doctrine\ORM\EntityRepository $entityRepository
     * @param int $id
     * @param string $breadcrumbRouteName
     * @return Object A doctrine entity.
     */
    public function findOrCreateEntity($entityRepository, $id, $breadcrumbRouteName = null)
    {
        if ($breadcrumbRouteName) {
            $breadcrumb = $this->get('apy_breadcrumb_trail');
        }

        if ($id) {
            $entity = $entityRepository->find($id);
            $this->createExceptionIfNotFound($entity);

            if ($breadcrumbRouteName) {
                $breadcrumb->add((string) $entity, $breadcrumbRouteName, array(
                    'id' => $id
                ))
                ->add('Edit');
            }
        } else {
            $className = $entityRepository->getClassName();
            $entity = new $className();

            if ($breadcrumbRouteName) {
                $breadcrumb->add('New');
            }
        }

        return $entity;
    }

    /**
     * Attempts to delete an entity.
     *
     * @param Doctrine\ORM\EntityRepository $entityRepository
     * @param int $id
     */
    public function deleteEntity($entityRepository, $id)
    {
        $entity = $entityRepository->find($id);
        $this->createExceptionIfNotFound($entity);

        if (! $entity->isDeletable()) {
            $this->createNotFoundException();
        }

        $em = $this->getDoctrine()->getEntityManager();

        $em->remove($entity);
        $em->flush();
    }
}

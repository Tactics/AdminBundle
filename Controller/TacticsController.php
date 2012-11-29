<?php

namespace Tactics\Bundle\AdminBundle\Controller;

use Tactics\TableBundle\QueryBuilderFilter\QueryBuilderFilter;
use Tactics\Bundle\AdminBundle\Entity\TacticsEntityInterface;

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
     * Attempts to delete an entity.
     *
     * @param TacticsEntityInterface $entity
     */
    public function deleteEntity(TacticsEntityInterface $entity)
    {
        if ( !$entity->isDeletable()) {
            throw $this->createNotFoundException();
        }

        $em = $this->getDoctrine()->getEntityManager();

        $em->remove($entity);
        $em->flush();
    }

    /**
     * Validates and saves object on POST.
     *
     * @param Symfony\Component\Form $form
     * @return bool form submission success.
     */
    public function handleFormSubmissionOnPOST($form)
    {
        $request = $this->getRequest();

        if ('POST' === $request->getMethod()) {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();

                $em->persist($form->getData());
                $em->flush();

                $this->setFlashSuccess('form.success', array(), 'TacticsAdminBundle');
                        
                return true;
            }
        }

        return false;
    }
    
    /**
     * Shortcut to the Doctrine repository
     * 
     * @return Doctrine\ORM\EntityRepository
     */
    public function getRepository($repository)
    {
        return $this->getDoctrine()->getRepository($repository);
    }
    
    /**
     * Adds a default breadcrumb
     * 
     * @param Object $entity
     * @param string $type
     */
    public function addBreadcrumb($entity, $type = 'show')
    {
        if ($entity && $entity->getId()) {
            $this->get('apy_breadcrumb_trail')
                ->add((string) $entity);
        }
        
        switch ($type)
        {
            case 'edit':
                if ($entity && $entity->getId()) {
                    $this->get('apy_breadcrumb_trail')
                    ->add(ucfirst($this->get('translator')->trans('actions.edit', array(), 'TacticsAdminBundle')));
                } else {
                    $this->get('apy_breadcrumb_trail')                
                        ->add(ucfirst($this->get('translator')->trans('actions.new', array(), 'TacticsAdminBundle')));
                }
                break;
                
            case 'show':
            default:
                break;
        }
    }

    /**
     * Translates the given message.
     *
     * @param string $id         The message id
     * @param array  $parameters An array of parameters for the message
     * @param string $domain     The domain for the message
     * @param string $locale     The locale
     *
     * @return string The translated string
     *
     * @api
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        return $this->get('translator')->trans($id, $parameters, $domain, $locale);
    }    
    
    /**
     * Sets a parameter in the flashbag
     * 
     * @param string $name
     * @param string $value
     */
    public function setFlash($name, $value)
    {        
        $this->get('session')->getFlashBag()->set($name, $value);
    }     

    /**
     * Sets a success message for display
     * 
     * @param string $message    The message to display
     * @param array  $parameters An array of parameters for the message
     * @param string $domain     The domain for the message
     * @param string $locale     The locale
     */
    public function setFlashSuccess($message, array $parameters = array(), $translationDomain = null, $locale = null)
    {
        $this->setFlash('message.success', $this->trans($message, $parameters, $translationDomain, $locale));
    }
    
    /**
     * Sets a warning message for display
     * 
     * @param string $message    The message to display
     * @param array  $parameters An array of parameters for the message
     * @param string $domain     The domain for the message
     * @param string $locale     The locale
     */
    public function setFlashWarning($message, array $parameters = array(), $translationDomain = null, $locale = null)
    {
        $this->setFlash('message.warning', $this->trans($message, $parameters, $translationDomain, $locale));
    }

    /**
     * Sets an error message for display
     * 
     * @param string $message    The message to display
     * @param array  $parameters An array of parameters for the message
     * @param string $domain     The domain for the message
     * @param string $locale     The locale
     */
    public function setFlashError($message, array $parameters = array(), $translationDomain = null, $locale = null)
    {
        $this->setFlash('message.error', $this->trans($message, $parameters, $translationDomain, $locale));
    }

    /**
     * Sets an info message for display
     * 
     * @param string $message    The message to display
     * @param array  $parameters An array of parameters for the message
     * @param string $domain     The domain for the message
     * @param string $locale     The locale
     */
    public function setFlashInfo($message, array $parameters = array(), $translationDomain = null, $locale = null)
    {
        $this->setFlash('message.info', $this->trans($message, $parameters, $translationDomain, $locale));
    }
}

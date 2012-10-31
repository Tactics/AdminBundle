<?php

namespace Tactics\Bundle\AdminBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\Container;

class UpdaterLoggableListener implements EventSubscriber
{
    private $container;
    private $annotationReader;
    private $user;
    private $annotationClass = 'Tactics\\Bundle\\AdminBundle\\Annotation\\UpdaterLoggable';
    
    public function __construct(Container $service_container) 
    {
        $this->container = $service_container;              
    }   
    
    public function getSubscribedEvents()
    {
        return array(
          'prePersist',
          'onFlush',          
        );
    }
    
    /**
     * Checks for persisted UpdaterLoggable objects
     * to update creation and modification dates
     *
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function prePersist(LifecycleEventArgs $args)
    {   
        $annotationReader = $this->getAnnotationReader();
        $user = $this->getUser();
        
        $entity = $args->getEntity();        
        $entityManager = $args->getEntityManager();
        
        $reflectionObject = new \ReflectionObject($entity);
        
        foreach ($reflectionObject->getProperties() as $reflectionProperty) {
            // fetch the @Tactics\UpdaterLoggable annotation from the annotation reader
            $annotation = $annotationReader->getPropertyAnnotation($reflectionProperty, $this->annotationClass);
            if (null !== $annotation) {   
                $reflectionProperty->setValue($entity, $user->getId());
                echo $reflectionProperty->name . ': @Tactics\UpdaterLoggable annotation FOUND: user=' . $user . '<br />';                
            }            
        }
            

        // check of object updaterLoggable Annotation heeft
        
        
    }
    
    /**
     * Looks for UpdaterLoggable objects being updated
     * to update modification date
     *
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function onFlush(LifecycleEventArgs $args)
    {
        $ea = $this->getEventAdapter($args);
        $om = $ea->getObjectManager();
        $uow = $om->getUnitOfWork();
    }
    
    
    /**
     * 
     * @return type
     */
    private function getAnnotationReader()
    {        
        if (!$this->annotationReader) {
            $this->annotationReader = $this->container->get('annotation_reader');
        }
        
        return $this->annotationReader;
    }
    
    /**
     * 
     * @return type
     */
    private function getUser()
    {
        
        if (!$this->user) {
            $this->user = $this->container->get('security.context')->getToken()->getUser();
        }
        
        return $this->user;
    }
    
    
//    public function postPersist(LifecycleEventArgs $args)
//    {
//        $entity = $args->getEntity();
//        $entityManager = $args->getEntityManager();
//
//        // perhaps you only want to act on some "Product" entity
//        if ($entity instanceof Product) {
//            // do something with the Product
//        }
//    }
}
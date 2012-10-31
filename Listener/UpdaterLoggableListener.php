<?php

namespace Tactics\Bundle\AdminBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
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
     * @param LifecycleEventArgs $eventArgs
     * @return void
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {   
        // security.context user
        $user = $this->getUser();        
        
        // user moet ingelogd zijn en een id hebben
        if (!is_object($user) || !method_exists($user, 'getId')) {
            exit();
        }       
        
        $annotationReader = $this->getAnnotationReader();
        
        $entity = $eventArgs->getEntity();        
        $reflectionObject = new \ReflectionObject($entity);
        
        foreach ($reflectionObject->getProperties() as $reflectionProperty) {
            // properties met @Tactics\UpdaterLoggable annotation ophalen
            $annotation = $annotationReader->getPropertyAnnotation($reflectionProperty, $this->annotationClass);
            if (null !== $annotation) {    
                $reflectionProperty->setAccessible(true);                
                if ($reflectionProperty->getValue($entity) === null) { // let manual changes be
                    $reflectionProperty->setValue($entity, $user->getId());
                }
            }            
        }
    }
    
    /**
     * Looks for UpdaterLoggable objects being updated
     * to update modification date
     *
     * @param OnFlushEventArgs $eventArgs
     * @return void
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        // security.context user
        $user = $this->getUser();
        
        // user moet ingelogd zijn en een id hebben
        if (!is_object($user) || !method_exists($user, 'getId')) {
            exit();
        }       
        
        $annotationReader = $this->getAnnotationReader();
        
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();
        
        foreach ($uow->getScheduledEntityUpdates() as $entity)
        {
            $reflectionObject = new \ReflectionObject($entity);
            foreach ($reflectionObject->getProperties() as $reflectionProperty) {
                // properties met @Tactics\UpdaterLoggable annotation ophalen
                $annotation = $annotationReader->getPropertyAnnotation($reflectionProperty, $this->annotationClass);
                if (null !== $annotation) {
                    // TODO: only update value if not in changeset yet
                    if ($annotation->type == 'updated_by') { // created_by only in prePersist
                        $reflectionProperty->setAccessible(true);
                        $reflectionProperty->setValue($entity, $user->getId());   
                    }                                        
                }            
            }
        }
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
}
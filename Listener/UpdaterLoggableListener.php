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
          'onFlush',
        );
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
            return;
        }

        $annotationReader = $this->getAnnotationReader();

        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() AS $entity) {
            $reflectionObject = new \ReflectionObject($entity);
            foreach ($reflectionObject->getProperties() as $reflectionProperty) {
                // properties met @Tactics\UpdaterLoggable annotation ophalen
                $annotation = $annotationReader->getPropertyAnnotation($reflectionProperty, $this->annotationClass);
                if (null !== $annotation) {
                    // TODO: only update value if not in changeset yet
                    if ($annotation->type == 'created_by') {
                        $reflectionProperty->setAccessible(true);
                        $reflectionProperty->setValue($entity, $user->getId());

                        $this->recomputeChangeSet($eventArgs, $entity);
                    }
                }
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity)
        {
            $reflectionObject = new \ReflectionObject($entity);
            foreach ($reflectionObject->getProperties() as $reflectionProperty) {
                // properties met @Tactics\UpdaterLoggable annotation ophalen
                $annotation = $annotationReader->getPropertyAnnotation($reflectionProperty, $this->annotationClass);
                if (null !== $annotation) {
                    // TODO: only update value if not in changeset yet
                    if ($annotation->type == 'updated_by') {
                        $reflectionProperty->setAccessible(true);
                        $reflectionProperty->setValue($entity, $user->getId());

                        $this->recomputeChangeSet($eventArgs, $entity);
                    }
                }
            }
        }
    }

    /**
     * Recomputes the change set for the object.
     *
     * @param OnFlushEventArgs $e The event arguments.
     */
    protected function recomputeChangeSet(OnFlushEventArgs $args, $entity)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $metadata = $em->getClassMetadata(get_class($entity));
        $uow->recomputeSingleEntityChangeSet($metadata, $entity);
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
        if (!$this->user && ($token = $this->container->get('security.context')->getToken())) {
            $this->user = $token->getUser();
        }

        return $this->user;
    }
}

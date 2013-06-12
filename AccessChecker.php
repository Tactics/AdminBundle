<?php

namespace Tactics\Bundle\AdminBundle;

use Symfony\Component\DependencyInjection\Container;

/**
 * Description of AccessChecker
 *
 * @author Joris
 */
class AccessChecker
{
    protected
        $container,     // the service container
        $user           // the user
    ;

    /**
     * the constructor
     *
     * @param \Symfony\Component\DependencyInjection\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Returns whether or not the given object
     * is accessible by the logged in user
     *
     * @param Object $object
     * @return boolean
     */
    public function checkUserAccess($object)
    {
        $em = $this->container->get('doctrine')->getEntityManager();
        $repo = $em->getRepository(get_class($object));

        if (!method_exists($repo, 'checkUserAccess')) {
            throw new \LogicException('checkUserAccess is not implemented on '.get_class($repo));
        }

        return $repo->checkUserAccess($object, $this->getUser());
    }

    /**
     * returns the user
     */
    private function getUser()
    {
        if (!$this->user) {
            $this->user = $this->container->get('security.context')->getToken()->getUser();
        }

        return $this->user;
    }
}

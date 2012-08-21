<?php

namespace Tactics\Bundle\AdminBundle\ObjectRouteResolver;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Tactics\Bundle\AdminBundle\ObjectRouteResolver\ObjectRouteResolver;
use Tactics\Bundle\AdminBundle\ObjectRouteResolver\Exception\UnknownClassException;

/**
 * @author Aaron Muylaert <aaron.muylaert at tactics.be>
 */
class ObjectRouteResolver implements ContainerAwareInterface
{
      /**
       * @var ContainerInterface $container A ContainerInterface instance.
       */
      protected $container;

      /**
       * @var array $defaultObjectRoutes An array containing the default object 
       * routes.
       */
      protected $defaultObjectRoutes = array();
      
      public function __construct(ContainerInterface $container)
      {
          $this->setContainer($container);

          $this->defaultObjectRoutes = $this->container->getParameter('object_routes');
      }

      /**
       * {@inheritdoc}
       */
      public function setContainer(ContainerInterface $container = null)
      {
          $this->container = $container;
      }

      /**
       * Retrieve default route for class from yml file.
       *
       * @param  string $class The class.
       * @return string The route name.
       */
      public function retrieveByClass($class)
      {
          if (false === array_key_exists($class, $this->defaultObjectRoutes)) {
              throw new UnknownClassException('Unknown class '.$class);       
          }

          return $this->defaultObjectRoutes[$class];
      }
}

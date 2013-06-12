<?php

namespace Tactics\Bundle\AdminBundle\ObjectRouteResolver;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Tactics\Bundle\AdminBundle\ObjectRouteResolver\ObjectRouteResolver;
use Tactics\Bundle\AdminBundle\ObjectRouteResolver\Exception\UnknownClassException;

/**
 * @author Aaron Muylaert <aaron.muylaert at tactics.be>
 */
class ObjectRouteResolver
{
      /**
       * @var array $defaultObjectRoutes An array containing the default object
       * routes.
       */
      protected $defaultObjectRoutes = array();

      public function __construct(array $routes)
      {
          $this->defaultObjectRoutes = $routes;
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
              throw new UnknownClassException($class);
          }

          return $this->defaultObjectRoutes[$class];
      }
}

<?php

namespace Tactics\Bundle\AdminBundle\ObjectRouteResolver\Exception;

class UnknownClassException extends ObjectRouteResolverException 
{
  
    const ERROR_MESSAGE = 'Cannot find object route for class "%s".';

    /**
     * Constructor
     *
     * @param string $className  The missing class
     */
    public function __construct($className)
    {
        parent::__construct(sprintf(self::ERROR_MESSAGE, $className));
    }
  
}

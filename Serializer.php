<?php

namespace Tactics\Bundle\AdminBundle;

use Symfony\Component\DependencyInjection\Container;

/**
 * Description of Serializer
 *
 * @author Jeroen
 */
class Serializer 
{
    protected 
        $container     // the service container
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
     * serializes the given value
     * 
     * @param mixed $value
     * @return string
     */
    public function serialize($value, $depthOfAssociations = 0)
    {
        if(is_array($value)) {
            $valueToSerialize = $this->serializeArray($value, $depthOfAssociations);
        }
        //check if get Id exists so that we pass an entity
        elseif (is_object($value) && method_exists($value, 'getId'))
        {
            $valueToSerialize = $this->serializeObject($value, $depthOfAssociations);
        }
        else {
            $valueToSerialize = $value;
        }
        
        return serialize($valueToSerialize);
    }
    
    /**
     * return unserialized value
     * 
     * @param mixed $value
     * @return mixed
     */
    public function unserialize($value)
    {
        $deserializedValue = unserialize($value);
        
        if(is_array($deserializedValue)) {
            $deserializedValue = $this->deserializeArray($deserializedValue);
        }
   
        return $deserializedValue;
    }
    
    /**
     * Serializes an Entity
     * 
     * @param Entity $object
     * @param int $depthOfAssociations
     * @return array
     */
    public function serializeObject($object, $depthOfAssociations)
    {
        $serializer = $this->container->get('serializer');
        return array('Object' => array('namespace' => get_class($object), 'entity' => $serializer->serialize($this->getSerializableEntity($object, $depthOfAssociations),'xml')));
    }
    
    /**
     * Serializes an array
     * 
     * @param array $array
     * @param int $depthOfAssociations
     * @return array
     */
    public function serializeArray($array, $depthOfAssociations)
    {
        foreach ($array as $key => $element) {
            if (is_array($element)) {
                $element = $this->serializeArray($element,$depthOfAssociations);
            }
            elseif (is_object($element) && method_exists($element, 'getId')) {
                $element = $this->serializeObject($element, $depthOfAssociations);
            }
            $array[$key] = $element;
        }
        
        return $array;
    }

    /**
     * Deserializes an array
     * 
     * @param array $array
     * @return array
     */
    public function deserializeArray($array)
    {
        
        foreach ($array as $arrayKey => $element) {
            if (is_array($element)) {
                foreach ($element as $key => $value) {
                    if($key === 'Object') {
                        $element = $this->deserializeObject($element[$key]);
                    }
                    elseif(is_array($value)) {
                        $element = $this->deserializeArray($array[$arrayKey]);
                    }
                }
            }
            $array[$arrayKey] = $element;
        }
        return $array;
    }
    
    /**
     * Deserializes an objectarray to an Entity
     * 
     * @param array $objectArray
     * @return Entity
     */
    public function deserializeObject($objectArray) 
    {
        $serializer = $this->container->get('serializer');
        
        return $serializer->deserialize($objectArray['entity'], $objectArray['namespace'], 'xml');
    }
    
    /**
     * Guesses a getter or setter method for a property of an Entity
     * 
     * @param Entity $object
     * @param Property $property
     * @param string $getOrSet
     * @return string|null
     */
    public function guessMethod($object, $property, $getOrSet)
    {
        $tmp = explode('_', $property);
        $str = '';
        $i = 0;
        while ($i < count($tmp))
        {
            $str .= ucfirst($tmp[$i]);
            $i++;
        }
        
        $getter = $getOrSet.$str;
        
        if (method_exists($object, $getter)) {
            return $getter;
        }
        else {
            return null;
        }
    }
    
    /**
     * Returns a Serializable entity
     * 
     * @param Entity $object
     * @param int $depthOfAssociations
     * @return null|\Tactics\Bundle\AdminBundle\namespace
     */
    public function getSerializableEntity($object, $depthOfAssociations)
    {   
        if(! ($depthOfAssociations >= 0)) {
            return null;
        }
        
        $reflectionObject = new \ReflectionObject($object);
        $properties = $reflectionObject->getProperties();
        
        $namespace = get_class($object);
        //if its proxyclass get the real classname (Todo : this is a very ugly way to do this)
        if(strrpos($namespace, 'roxies')) {
            $namespace = substr($namespace, 15);
        }
        //cant construct DateTime or DateTimeZone, constructor needs a parameter
        if(strrpos($namespace, 'ateTime'))
        {
            return null;
        }
        $returnObject = new $namespace();
        
        
        foreach($properties as $property) {
            $property = $property->getName();
            
            $getMethod = $this->guessMethod($object, $property,'get');
            if($getMethod && $object->$getMethod()) {
                $propertyValue = $object->$getMethod();
                //Property is collection/array
                if(is_array($propertyValue) || method_exists($propertyValue, 'toArray')) {
                    $idArray = array();
                    foreach($propertyValue as $propvalue) {
                        
                        //if it's  an object save its Id
                        if(is_object($propvalue) && method_exists($propvalue, 'getId')) {
                        
                            $propertyValue = $depthOfAssociations > 0 ? $this->getSerializableEntity($propvalue, $depthOfAssociations-1) : null;
                        }
                        //if not an object save value itself
                        else {
                            $idArray[] = $propvalue;
                        }
                    }
                    $propertyValue = $idArray;
                }
                //Property is association to other entity
                elseif (is_object($propertyValue)) {
                        $propertyValue = $depthOfAssociations > 0 ? $this->getSerializableEntity($propertyValue, $depthOfAssociations-1) : null;
                }
                //If a setter for this property exists, set its value
                $setter = $this->guessMethod($object, $property, 'set');
                if(method_exists($object, $setter) && $propertyValue !== null) {
                    $returnObject->$setter($propertyValue);
                }
            
            }
        }
        
        return $returnObject;
    }
}

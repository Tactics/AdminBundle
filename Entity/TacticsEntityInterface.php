<?php

namespace Tactics\Bundle\AdminBundle\Entity;

/**
 * Generic interface all entities in Tactics projects need to implement.
 */
interface TacticsEntityInterface
{
    /**
     * Returns default text representation for the entity.
     * 
     * @return string
     */
    public function __toString();
    
    /**
     * Determines whether the entity may be removed.
     * 
     * @return boolean
     */
    public function isDeletable();
}
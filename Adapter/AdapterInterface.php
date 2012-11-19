<?php

namespace Tactics\Bundle\AdminBundle\Adapter;

interface AdapterInterface extends \Vich\UploaderBundle\Adapter\AdapterInterface
{
    /**
     * Update the object
     *
     * @param EventArgs $e The event arguments
     */
    public function update(\Doctrine\Common\EventArgs $args);
}

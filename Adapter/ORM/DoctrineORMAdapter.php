<?php

namespace Tactics\Bundle\AdminBundle\Adapter\ORM;

use Tactics\Bundle\AdminBundle\Adapter\AdapterInterface;
use Doctrine\Common\EventArgs;

class DoctrineORMAdapter extends \Vich\UploaderBundle\Adapter\ORM\DoctrineORMAdapter implements AdapterInterface
{
    /**
     * {@inheritdoc}
     */
    public function update(EventArgs $args)
    {
        $obj = $this->getObjectFromArgs($args);
        $em = $args->getEntityManager();
        $em->persist($obj);
        $em->flush();
    }
    
}

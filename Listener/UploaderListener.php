<?php

namespace Tactics\Bundle\AdminBundle\Listener;

use Doctrine\Common\EventArgs;

class UploaderListener extends \Vich\UploaderBundle\EventListener\UploaderListener
{
    /**
     * The events the listener is subscribed to.
     *
     * @return array The array of events.
     */
    public function getSubscribedEvents()
    {
        return array(
            'postLoad',
            'postPersist',
            'postUpdate',
            'postRemove',
        );
    }

    /**
     * Checks for for file to upload.
     *
     * @param \Doctrine\Common\EventArgs $args The event arguments.
     */
    public function postPersist(EventArgs $args)
    {
        $obj = $this->adapter->getObjectFromArgs($args);
        if ($this->isUploadable($obj)) {
            $this->storage->upload($obj);
            $this->adapter->update($args);
        }
    }

    /**
     * Update the file and file name if necessary.
     *
     * @param EventArgs $args The event arguments.
     */
    public function postUpdate(EventArgs $args)
    {
        $obj = $this->adapter->getObjectFromArgs($args);

        if ($this->isUploadable($obj)) {
            $this->storage->upload($obj);
            $this->adapter->update($args);
        }
    }
}

<?php

namespace Tactics\Bundle\AdminBundle\Storage;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileSystemStorage extends \Vich\UploaderBundle\Storage\FileSystemStorage
{
    /**
     * {@inheritDoc}
     */
    public function upload($obj)
    {
        $mappings = $this->factory->fromObject($obj);
        foreach ($mappings as $mapping) {
            $file = $mapping->getPropertyValue($obj);
            if (is_null($file) || !($file instanceof UploadedFile)) {
                continue;
            }

            if ($mapping->hasNamer()) {
                $name = $mapping->getNamer()->name($obj, $mapping->getProperty());
            } else {
                $name = $file->getClientOriginalName();
                var_dump($name);
            }

            $file->move($mapping->getUploadDir($obj), $name);

            $mapping->getFileNameProperty()->setValue($obj, $name);

            // Set file property to null, so Doctrine postUpdate won't be
            // triggered after postPersist.
            $mapping->getProperty()->setValue($obj, null);
        }
    }
    
}

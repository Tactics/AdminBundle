<?php

namespace Tactics\Bundle\AdminBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class UpdaterLoggable extends Annotation
{    
    /**
     * type can be created_by or updated_by
     */
    public $type;
}
<?php

namespace Tactics\Bundle\AdminBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class TacticsAdminBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }    
}

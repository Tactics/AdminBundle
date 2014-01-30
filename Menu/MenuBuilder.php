<?php

namespace Tactics\Bundle\AdminBundle\Menu;

use Symfony\Component\Security\Core\SecurityContextInterface;

class MenuBuilder
{
    private $security;

    public function __construct(SecurityContextInterface $security)
    {
        $this->security = $security;
    }

    public function build(array $menu)
    {
        // Remove actions not allowed if option role is used
        foreach($menu as $menuIndex1 => $item) {
            foreach($item as $menuIndex2 => $item2){
                // Has subactions
                if(isset($item2['actions'])) {
                    foreach($item2['actions'] as $menuIndex3 => $action){
                        if (isset($action['role'])) {
                            $remove = true;
                            foreach ((array) $action['role'] as $role) {
                                if ($this->security->isGranted($role)) {
                                    $remove = false;
                                    break;
                                }
                            }

                            if ($remove) {
                                unset($menu[$menuIndex1][$menuIndex2]['actions'][$menuIndex3]);
                            }
                        }
                    }
                    if(count($menu[$menuIndex1][$menuIndex2]['actions']) == 0){
                        unset($menu[$menuIndex1][$menuIndex2]);
                    }
                }
                // Has direct route and not allowed
                else if(isset($item2['role']) && ! $this->security->isGranted($item2['role'])){
                    unset($menu[$menuIndex1][$menuIndex2]);
                }
            }
            // If subarray empty
            if(count($menu[$menuIndex1]) == 0) {
                unset($menu[$menuIndex1]);
            }
        }

        return $menu;
    }
}

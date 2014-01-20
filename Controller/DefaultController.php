<?php

namespace Tactics\Bundle\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;

use Tactics\Bundle\PersoonBundle\Model\PersoonPeer;
use Tactics\Bundle\PersoonBundle\Model\Persoon;

use Tactics\Bundle\AdminBundle\Show\Show;

class DefaultController extends \Tactics\Bundle\AdminBundle\Controller\TacticsController
{

    private function removeNotAllowedItems($menu)
    {
        // Remove actions not allowed if option role is used
        foreach($menu as $menuIndex1 => $item) {
            foreach($item as $menuIndex2 => $item2){
                // Has subactions
                if(isset($item2['actions'])) {
                    foreach($item2['actions'] as $menuIndex3 => $action){
                        if(isset($action['role']) && ! $this->isGranted($action['role'])){
                            unset($menu[$menuIndex1][$menuIndex2]['actions'][$menuIndex3]);
                        }
                    }
                    if(count($menu[$menuIndex1][$menuIndex2]['actions']) == 0){
                        unset($menu[$menuIndex1][$menuIndex2]);
                    }
                }
                // Has direct route and not allowed
                else if(isset($item2['role']) && ! $this->isGranted($item2['role'])){
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


    /**
     * @return type
     */
    public function dashboardAction()
    {
        return $this->render('TacticsAdminBundle:Default:dashboard.html.twig');
    }

    /**
     * Rendert het menu. Als top true is, wordt het top_menu gerendert. Default
     * het side_menu.
     *
     * @param boolean $top
     * @return Response
     */
    public function menuAction($top = false)
    {
        $menu = $this->container->getParameter('tactics_menu');
        $menu = $this->removeNotAllowedItems($menu);


        $template = $top ? 'TacticsAdminBundle:Default:top_menu.html.twig' : 'TacticsAdminBundle:Default:side_menu.html.twig';

        return $this->render($template, array(
            'menu' => $menu
        ));
    }

    public function subnavAction()
    {
        $menu = $this->container->getParameter('tactics_menu');
        $menu = $this->removeNotAllowedItems($menu);

        return $this->render('TacticsAdminBundle:Default:subnav.html.twig', array(
            'menu' => $menu
        ));
    }

    /**
     * Maakt een acties dropdown adhv doorgegeven array. Als acties leeg is wordt
     * button disabled.
     *
     * @param Array $acties
     * @return Response
     */
    public function actiesAction($acties = array())
    {
        return $this->render('TacticsAdminBundle:Default:acties.html.twig', array(
            'acties' => $acties
        ));
    }
}

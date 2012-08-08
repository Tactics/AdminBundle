<?php

namespace Tactics\Bundle\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;

use Tactics\Bundle\PersoonBundle\Model\PersoonPeer;
use Tactics\Bundle\PersoonBundle\Model\Persoon;

use Tactics\Bundle\AdminBundle\Show\Show;

/**
 * @Breadcrumb("Home")
 */
class DefaultController extends Controller
{
    /**
     * @Breadcrumb("Dashboard")
     * 
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
      
      $template = $top ? 'TacticsAdminBundle:Default:top_menu.html.twig' : 'TacticsAdminBundle:Default:side_menu.html.twig';
      
      return $this->render($template, array(
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

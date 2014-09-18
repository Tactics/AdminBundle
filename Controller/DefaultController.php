<?php

namespace Tactics\Bundle\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;

use Symfony\Component\HttpFoundation\Response;
use Tactics\Bundle\PersoonBundle\Model\PersoonPeer;
use Tactics\Bundle\PersoonBundle\Model\Persoon;

use Tactics\Bundle\AdminBundle\Show\Show;

class DefaultController extends \Tactics\Bundle\AdminBundle\Controller\TacticsController
{
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
        $menu = $this->container
            ->get('tactics.menu_builder')
            ->build($this->container->getParameter('tactics_menu'));

        $template = $top ? 'TacticsAdminBundle:Default:top_menu.html.twig' : 'TacticsAdminBundle:Default:side_menu.html.twig';

        return $this->render($template, array(
            'menu' => $menu
        ));
    }

    public function subnavAction()
    {
        $menu = $this->container
            ->get('tactics.menu_builder')
            ->build($this->container->getParameter('tactics_menu'));

        $response = new Response();
        $response->setSharedMaxAge(3600);

        return $this->render(
            'TacticsAdminBundle:Default:subnav.html.twig',
            array('menu' => $menu),
            $response
        );
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

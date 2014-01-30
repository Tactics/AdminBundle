<?php

namespace Tactics\Bundle\AdminBundle\Tests\Menu;

use Tactics\Bundle\AdminBundle\Menu\MenuBuilder;

class MenuBuilderTest extends \PHPUnit_Framework_TestCase
{
    private $menu;
    private $security;
    private $builder;

    public function setUp()
    {
        $this->security = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContextInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('isGranted', 'getToken', 'setToken'))
            ->getMock();

        $this->menu = array(
            'Airplanes' => array(),
            'Animals' => array('Dogs' => array()),
        );

        $this->builder = new MenuBuilder($this->security);
    }

    /**
     * @test
     */
    public function it_removes_item_when_it_has_no_children()
    {
        $menu = $this->builder->build($this->menu);

        $this->assertFalse(isset($menu['Airplanes']));
        $this->assertTrue(isset($menu['Animals']));
    }
}

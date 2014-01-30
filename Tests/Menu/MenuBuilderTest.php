<?php

namespace Tactics\Bundle\AdminBundle\Tests\Menu;

use Tactics\Bundle\AdminBundle\Menu\MenuBuilder;

class MenuBuilderTest extends \PHPUnit_Framework_TestCase
{
    private $menu;
    private $security;
    private $builder;
    private $roleMap = array(
        'SEARCH_FISH' => false,
        'SEARCH_CATS' => false,
    );

    public function setUp()
    {
        $this->security = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContextInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('isGranted', 'getToken', 'setToken'))
            ->getMock();

        $this->menu = array(
            'Airplanes' => array(), // item
            'Animals' => array(
                'Dogs' => array(), // subitem
                'Cats' => array(
                    'role' => 'SEARCH_CATS',
                ),
                'Fish' => array(
                    'actions' => array(
                        array('label' => 'Search', 'role' => 'SEARCH_FISH') // action
                    ),
                ),
            ),
        );

        $this->builder = new MenuBuilder($this->security);
    }

    /**
     * @test
     */
    public function it_removes_item_when_it_has_no_children()
    {
        $menu = $this->buildMenu();

        $this->assertFalse(isset($menu['Airplanes']));
        $this->assertTrue(isset($menu['Animals']));
    }

    /**
     * @test
     */
    public function it_removes_subitem_when_user_does_not_have_role()
    {
        $this->whenUserDoesNotHaveRole('SEARCH_CATS');

        $menu = $this->buildMenu();

        $this->assertFalse(isset($menu['Animals']['Cats']));
    }

    /**
     * @test
     */
    public function it_removes_action_when_user_does_not_have_role()
    {
        $this->whenUserDoesNotHaveRole('SEARCH_FISH');

        $menu = $this->buildMenu();

        $this->assertFalse(isset($menu['Animals']['Fish']['actions'][0]));
    }

    private function whenUserHasRole($role)
    {
        $this->roleMap[$role] = true;
    }

    private function whenUserDoesNotHaveRole($role)
    {
        $this->roleMap[$role] = false;
    }

    private function buildMenu()
    {
        // For using in Closure (PHP 5.3 >.<)
        $roleMap = $this->roleMap;

        // Set up configured user roles
        $this->security->expects($this->any())
            ->method('isGranted')
            ->will($this->returnCallback(function($arg) use ($roleMap) {
                foreach ($roleMap as $roleName => $userHasRole) {
                    if ($arg === $roleName) {
                        return $userHasRole;
                    }
                }
            }));

        return $this->builder->build($this->menu);
    }
}

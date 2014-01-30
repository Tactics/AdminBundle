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
        'SEARCH_DEER' => false,
        'DELETE_FISH' => false,
        'FISH_MASTER' => false,
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
                        array('label' => 'Search fish', 'role' => 'SEARCH_FISH'), // action
                        array('label' => 'Create fish'),
                        array('label' => 'Delete fish', 'role' => array('DELETE_FISH', 'FISH_MASTER'))
                    ),
                ),
                'Deer' => array(
                    'actions' => array(
                        array('label' => 'Search deer', 'role' => 'SEARCH_DEER'), // action
                    ),
                )
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

    /**
     * @test
     * @dataProvider setupMaps
     */
    public function it_removes_action_when_user_does_not_have_one_of_roles($roleMap, $expected)
    {
        $menu = $this->buildMenu($roleMap);

        $this->assertEquals($expected, isset($menu['Animals']['Fish']['actions'][2]));
    }

    public function setupMaps()
    {
        // Using array_merge to clone arrays.
        $userHasRoleFishMaster = array_merge(array(), $this->roleMap);
        $userHasRoleFishMaster['FISH_MASTER'] = true;

        $userHasRoleDeleteFish = array_merge(array(), $this->roleMap);
        $userHasRoleDeleteFish['DELETE_FISH'] = true;

        $userDoesNotHaveRoles = array_merge(array(), $this->roleMap);

        return array(
            array($userHasRoleFishMaster, true),
            array($userHasRoleDeleteFish, true),
            array($userDoesNotHaveRoles, false),
        );
    }

    /**
     * @test
     */
    public function it_removes_subitem_when_it_does_not_contain_actions()
    {
        $this->whenUserDoesNotHaveRole('SEARCH_DEER');

        $menu = $this->buildMenu();

        $this->assertFalse(isset($menu['Animals']['Deer']));
    }

    private function whenUserHasRole($role)
    {
        $this->roleMap[$role] = true;
    }

    private function whenUserDoesNotHaveRole($role)
    {
        $this->roleMap[$role] = false;
    }

    /**
     * Only pass roles array for advanced configuration that can't be achieved by
     * using the helper methods that directly manipulate the roleMap instance variable.
     */
    private function buildMenu(array $roles = array())
    {
        // For using in Closure (PHP 5.3 >.<)
        $roleMap = $roles ? $roles : $this->roleMap;

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

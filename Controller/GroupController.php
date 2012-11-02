<?php

namespace Tactics\Bundle\AdminBundle\Controller;

//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\UserBundle\Controller\RegistrationController as BaseController;

class GroupController extends BaseController
{
    private function recursiveShit($roleHierarchy, $role)
    {
        if (isset($roleHierarchy[$role])) {
            $subRoles[] = $role;
            // recursion shit here 
            $map[$main][$role] = recursiveShit($roleHierarchy, $roleHierarchy[$role]);              
        }
    }
    
    private function buildNestedRoleMap($roleHierarchy)
    {
        // eerst alles roles associative maken om makkelijker te werken
        foreach ($roleHierarchy as $main => $roles) {
            $roleHierarchy[$main] = array_combine($roles, $roles);            
        }
        
        $map = $roleHierarchy;
        $subRoles = array();
        foreach ($roleHierarchy as $main => $roles) {
            foreach ($roles as $role) {
                $map[$main][$role] = $this->recursiveShit($roleHierarchy, $role);
                
            }
        }
        
        // deleten van mainroles die eigenlijk een subrole zijn
        foreach ($subRoles as $role)
        {
            unset($map[$role]);
        }
        
        return $map;
    }
    
    /**
     * Show the new form
     */
    public function newAction()
    {        
        $form = $this->container->get('fos_user.group.form');
        $formHandler = $this->container->get('fos_user.group.form.handler');

        $process = $formHandler->process();
        if ($process) {
            $this->setFlash('fos_user_success', 'group.flash.created');
            $parameters = array('groupname' => $form->getData('group')->getName());
            $url = $this->container->get('router')->generate('fos_user_group_show', $parameters);

            return new RedirectResponse($url);
        }
        
        // Extending FOSUserBundle
//        $roleHierarchy = $this->container->getParameter('security.role_hierarchy.roles');
//        $roleHierarchy = $this->buildNestedRoleMap($roleHierarchy);
        
        $roleHierarchy = array (
            'ROLE_SUPER_ADMIN' => array (
                'ROLE_ALLOWED_TO_SWITCH' => 'ROLE_ALLOWED_TO_SWITCH',
                'ROLE_ADMIN' => array (
                    'ROLE_USER' => 'ROLE_USER'
                )
            )
        );

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Group:new.html.'.$this->getEngine(), array(
            'form' => $form->createview(),
            'role_hierarchy' => $roleHierarchy
        ));
    }
    
    /**
     * Edit one group, show the edit form
     */
    public function editAction($groupname)
    {
        $group = $this->findGroupBy('name', $groupname);
        $form = $this->container->get('fos_user.group.form');
        $formHandler = $this->container->get('fos_user.group.form.handler');

        $process = $formHandler->process($group);
        if ($process) {
            $this->setFlash('fos_user_success', 'group.flash.updated');
            $groupUrl =  $this->container->get('router')->generate('fos_user_group_show', array('groupname' => $group->getName()));

            return new RedirectResponse($groupUrl);
        }

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Group:edit.html.'.$this->getEngine(), array(
            'form'      => $form->createview(),
            'groupname'  => $group->getName(),
        ));
    }
    
}

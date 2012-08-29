<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tactics\Bundle\AdminBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\GroupFormType as BaseType;

class GroupFormType extends BaseType
{
    private $roles;
    
    /**
     * @param string $class The Group class name
     * @param array $roles The security role hierarchy
     */
    public function __construct($class, $roles)
    {
        parent::__construct($class);
        $this->roles = $roles;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        // add your custom field
        foreach ($this->roles as $mainrole => $subroles)
        {            
            $builder->add($mainrole, 'checkbox', array(                                                                   
                'widget_type'  => 'inline',
                'property_path' => false                
            ));
            
            foreach ($subroles as $subrole)
            {
                $builder->add($subrole, 'checkbox', array(
                    'widget_type'  => 'inline',
                    'property_path' => false
                ));
            } 
            
//            $builder->add('roles', 'choice', array(
//                'choices' => $this->roles,
//                'multiple' => true,
//                'attr' => array(
//                    'class' => 'chosen'
//                )
//            ));
        }
        
    }

    public function getName()
    {
        return 'tactics_user_group';
    }
}

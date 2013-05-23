<?php
namespace Tactics\Bundle\AdminBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

interface FormHandlerInterface
{
    /**
     * processes the form
     *
     * @param \Symfony\Component\Form\FormInterface $form
     * @return boolean
     */
    public function process(FormInterface $form);

    /**
     * Returns the currently used entity manager
     *
     * @return \Doctrine\Common\Persistence\ObjectManager|Doctrine\Common\Persistence\ObjectManager
     */
    public function getEntityManager();

    /**
     * Replace the currently used entity manager
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $em
     * @return EntityFormHandler
     */
    public function setEntityManager(ObjectManager $em);
}
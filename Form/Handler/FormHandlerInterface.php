<?php

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

interface FormHandlerInterface
{
    /**
     * constructor
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\DependencyInjection\Container $container
     */
    public function __construct(Request $request, ObjectManager $em, Session $session, Translator $translator);

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
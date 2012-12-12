<?php

namespace Tactics\Bundle\AdminBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class EntityFormHandler
{
    /**
     * @var Symfony\Component\HttpFoundation\Request $request
     */
    protected $request;            

    /**
     * @var Doctrine\Common\Persistence\ObjectManager
     */
    protected $em;

    /**
     * @var Symfony\Component\HttpFoundation\Session\Session $session
     */
    protected $session;

    /**
     * @var Symfony\Bundle\FrameworkBundle\Translation\Translator $translator
     */
    protected $translator;

    /**
     * constructor
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\DependencyInjection\Container $container
     */
    public function __construct(Request $request, ObjectManager $em, Session $session, Translator $translator)
    {
        $this->request = $request;
        $this->em = $em;
        $this->session = $session;
        $this->translator = $translator;
    }
    
    /**
     * processes the form
     * 
     * @param \Symfony\Component\Form\FormInterface $form
     * @return boolean
     */
    public function process(FormInterface $form)
    {
        if ('POST' === $this->request->getMethod()) {
            $form->bind($this->request);

            if ($form->isValid()) {
                $this->onSuccess($form);

                return true;
            }
        }

        return false;
    }

    /**
     * action that is executed when form is successfully validated
     * 
     * @param \Symfony\Component\Form\FormInterface $form
     */
    protected function onSuccess(FormInterface $form)
    {
        $em = $this->container->get('doctrine')->getEntityManager();
        $em->persist($form->getData());
        $em->flush();
        
        $this->setFlashSuccess();                
    }
    
    /**
     * Sets a success message for display
     * 
     * @param string $message    The message to display
     * @param array  $parameters An array of parameters for the message
     * @param string $domain     The domain for the message
     * @param string $locale     The locale
     */
    protected function setFlashSuccess($message = 'form.success', array $parameters = array(), $translationDomain = 'TacticsAdminBundle', $locale = null)
    {
        $this->session->getFlashBag()->set(
            'message.success', 
            $this->translator->trans($message, $parameters, $translationDomain, $locale)
        );
    }
}

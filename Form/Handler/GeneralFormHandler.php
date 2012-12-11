<?php

namespace Tactics\Bundle\AdminBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\Container;

class GeneralFormHandler
{
    protected $request;            
    protected $container;

    /**
     * constructor
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\DependencyInjection\Container $container
     */
    public function __construct(Request $request, Container $container)
    {
        $this->request = $request;
        $this->container = $container;
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
        
        $this->setFlashSuccess('form.success', array(), 'TacticsAdminBundle');                
    }
    
    /**
     * Sets a success message for display
     * 
     * @param string $message    The message to display
     * @param array  $parameters An array of parameters for the message
     * @param string $domain     The domain for the message
     * @param string $locale     The locale
     */
    protected function setFlashSuccess($message, array $parameters = array(), $translationDomain = null, $locale = null)
    {
        $session = $this->container->get('session');
        $translator = $this->container->get('translator');        
        $session->getFlashBag()->set('message.success', $translator->trans($message, $parameters, $translationDomain, $locale));
    }
}

?>

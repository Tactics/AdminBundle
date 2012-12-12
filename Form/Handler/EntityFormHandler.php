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
        $this->em->persist($form->getData());
        $this->em->flush();
        
        $this->setFlashSuccess();                
    }

    /**
     * Ensure that removed items in collections actually get removed.
     *
     * @param \Symfony\Component\Form\FormInterface $form
     */
    protected function cleanupCollections(FormInterface $form)
    {
        $children = $form->getChildren();

        foreach ($children as $childForm) {
            $data = $childForm->getData();
            if ($data instanceof Collection) {

                // Get the child form objects and compare the data of each child against the object's current collection
                $proxies = $childForm->getChildren();
                foreach ($proxies as $proxy) {
                    $entity = $proxy->getData();
                    if (!$data->contains($entity)) {
                        $this->em->remove($entity);
                    }
                }
            }
        }
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

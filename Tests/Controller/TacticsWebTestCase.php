<?php

namespace Tactics\Bundle\AdminBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class TacticsWebTestCase extends WebTestCase
{
    
    /**
     * Returns an authentificated client
     * 
     * @return test.Client
     */
    public function getAuthClient() {
        $client = static::createClient(array(), array('PHP_AUTH_USER' => 'test@tactics.be', 'PHP_AUTH_PW' => 'tijdelijk'));
        $client->followRedirects(true);
        $this->client = $client;
        return $this->client;
    }
    
    // POSSIBILITY 2
    /*public function createAuthentifcatedClient($firewall, $role = 'ROLE_USER')
    {   
        $client = static::createClient(); 
        $container = static::$kernel->getContainer();        
        $client->getCookieJar()->set(new Cookie($container->get('session')->getName(), true));        
        $token = new UsernamePasswordToken('admin', null, $firewall, $role);        
        $container->get('security.context')->setToken($token);        
        $container->get('session')->set('_security_' . $firewall, serialize($token));
        
        return $client;
        
    }*/
    
    /**
     * Returns the url 
     * 
     * @param string $route
     * @return string $url
     */
    public function getUrlFromRoute($route, $options = array()) {
        //$router = $client->getContainer()->get('router');
        $router = $this->client->getKernel()->getContainer()->get('router');
        return $router->generate($route, $options);
    }
}

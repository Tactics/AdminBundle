<?php

namespace Tactics\Bundle\AdminBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Doctrine\Common\Persistence\ObjectManager;

class TacticsWebTestCase extends WebTestCase
{
    public function setUp()
    {
        static::$kernel = static::createKernel(array(
            'environment' => 'test',
            'debug' => true,
        ));

        static::$kernel->boot();
    }

    public function loadFixtures(ObjectManager $om, $dir = null)
    {
        $loader    = new \Nelmio\Alice\Loader\Yaml();
        $persister = new \Nelmio\Alice\ORM\Doctrine($om);

        $this->regenerateSchema($om);

        $persister->persist($loader->load($dir));
    }

    public function regenerateSchema(ObjectManager $om)
    {
        $metadatas = $om->getMetadataFactory()->getAllMetadata();

        if (! empty($metadatas)) {
            $tool = new \Doctrine\ORM\Tools\SchemaTool($om);
            $tool->dropSchema($metadatas);
            $tool->createSchema($metadatas);
        }
    }

    public function getFosUserClient($usernameOrEmail, $password)
    {
        $manager = $this->get('fos_user.user_manager');
        $em      = $this->get('doctrine')->getEntityManager();

        $user = $manager->findUserByUsernameOrEmail($usernameOrEmail);

        // TODO FOSUser __construct sets a salt. Can I do anything about this?
        $user->setPassword($user->getPassword() . '{' . $user->getSalt() . '}');

        $manager->updateUser($user);
        $manager->reloadUser($user);

        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => $usernameOrEmail,
            'PHP_AUTH_PW'   => $password,
        ));

        $client->followRedirects(true);

        return $client;
    }

    public function get($serviceId)
    {
        return static::$kernel->getContainer()->get($serviceId);
    }

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

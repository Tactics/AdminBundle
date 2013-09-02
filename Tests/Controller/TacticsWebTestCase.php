<?php

namespace Tactics\Bundle\AdminBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;
use Doctrine\Common\Persistence\ManagerRegistry;

class TacticsWebTestCase extends WebTestCase
{
    /**
     * @var array
     */
    static private $cachedMetadatas = array();

    /**
     * @var array
     */
    static private $cachedMetadatasSerialized = array();

    public function setUp()
    {
       static::$kernel = static::createKernel(array(
           'environment' => 'test',
           'debug' => true,
       ));

       static::$kernel->boot();
    }

    /**
     * Determine if the Fixtures that define a database backup have been
     * modified since the backup was made.
     *
     * @param array $classNames The fixture classnames to check
     * @param string $backup The fixture backup SQLite database file path
     *
     * @return bool TRUE if the backup was made since the modifications to the
     * fixtures; FALSE otherwise
     */
    protected function isBackupUpToDate(array $files, $backup)
    {
        $backupLastModifiedDateTime = new \DateTime();
        $backupLastModifiedDateTime->setTimestamp(filemtime($backup));

        foreach ($files as $file) {
            $fixtureLastModifiedDateTime = new \DateTime('@'.filemtime($file));
            if ($backupLastModifiedDateTime < $fixtureLastModifiedDateTime) {
                return false;
            }
        }

        return true;
    }

    public function loadFixtures($files, $omName = null, $registryName = 'doctrine', $cache = true)
    {
        $files = (array) $files;
        $container = static::$kernel->getContainer();

        $registry = $container->get($registryName);
        if ($registry instanceof ManagerRegistry) {
            $om = $registry->getManager($omName);
            $type = $registry->getName();
        } else {
            $om = $registry->getEntityManager($omName);
            $type = 'ORM';
        }

        $cacheDriver = $om->getMetadataFactory()->getCacheDriver();

        if ($cacheDriver) {
            $cacheDriver->deleteAll();
        }

        if ('ORM' === $type) {
            $connection = $om->getConnection();
            if ($connection->getDriver() instanceOf SqliteDriver) {
                $params = $connection->getParams();
                $name = isset($params['path']) ? $params['path'] : $params['dbname'];

                if (!isset(self::$cachedMetadatas[$omName])) {
                    self::$cachedMetadatas[$omName] = $om->getMetadataFactory()->getAllMetadata();
                    self::$cachedMetadatasSerialized[$omName] = self::$cachedMetadatas[$omName];
                }
                $metadatas = self::$cachedMetadatas[$omName];

                if ($cache) {
                    $backup = $container->getParameter('kernel.cache_dir') . '/test_' . md5(self::$cachedMetadatasSerialized[$omName] . serialize($files)) . '.db';
                    if (file_exists($backup) && $this->isBackupUpToDate($files, $backup)) {
                        $om->flush();
                        $om->clear();

                        copy($backup, $name);

                        $this->postFixtureRestore();

                        return;
                    }
                }

                $schemaTool = new SchemaTool($om);
                $schemaTool->dropDatabase($name);
                if (!empty($metadatas)) {
                    $schemaTool->createSchema($metadatas);
                }
                $this->postFixtureSetup();
            }
        }

        $loader    = new \Nelmio\Alice\Loader\Yaml();
        $persister = new \Nelmio\Alice\ORM\Doctrine($om);

        foreach ($files as $fixture) {
            $persister->persist($loader->load($fixture));
        }
//        $loader = $this->getFixtureLoader($container, $classNames);

        if (isset($name) && isset($backup)) {
            copy($name, $backup);
        }
    }

    /**
     * Callback function to be executed after Schema creation.
     * Use this to execute acl:init or other things necessary.
     */
    protected function postFixtureSetup()
    {

    }

    /**
     * Callback function to be executed after Schema restore.
     */
    protected function postFixtureRestore()
    {

    }

    public function getFosUserClient($usernameOrEmail, $password)
    {
        $manager = $this->get('fos_user.user_manager');
        $em      = $this->get('doctrine')->getEntityManager();

        $user = $manager->findUserByUsernameOrEmail($usernameOrEmail);

        // TODO FOSUser __construct sets a salt. Can I do anything about this?
        $user->setPassword($user->getPassword() . '{' . $user->getSalt() . '}');

        $manager->updateUser($user);

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
    public function getAuthClient()
    {
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
    public function getUrlFromRoute($route, $options = array())
    {
        //$router = $client->getContainer()->get('router');
        $router = $this->client->getKernel()->getContainer()->get('router');
        return $router->generate($route, $options);
    }

    /**
     * Makes a get request to a route and returns a crawler object
     *
     * @param $route
     * @return mixed
     */
    public function request($client, $route, $params = array(), $method = 'GET')
    {
        return $client->request($method, $this->get('router')->generate($route, $params));
    }
}

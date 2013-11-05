<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Log;

use Zend\Log\Writer\Db as DbWriter;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

/**
 * @group      Zend_Log
 */
class LoggerAbstractServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceManager;

    /**
     * Set up LoggerAbstractServiceFactory and loggers configuration.
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->serviceManager = new ServiceManager(new ServiceManagerConfig(array(
            'abstract_factories' => array('Zend\Log\LoggerAbstractServiceFactory'),
        )));

        $this->serviceManager->setService('Config', array(
            'log' => array(
                'Application\Frontend' => array(),
                'Application\Backend'  => array(),
            ),
        ));
    }

    /**
     * @return array
     */
    public function providerValidLoggerService()
    {
        return array(
            array('Application\Frontend'),
            array('Application\Backend'),
        );
    }

    /**
     * @return array
     */
    public function providerInvalidLoggerService()
    {
        return array(
            array('Logger\Application\Unknown'),
            array('Logger\Application\Frontend'),
            array('Application\Backend\Logger'),
        );
    }

    /**
     * @param string $service
     * @dataProvider providerValidLoggerService
     */
    public function testValidLoggerService($service)
    {
        $actual = $this->serviceManager->get($service);
        $this->assertInstanceOf('Zend\Log\Logger', $actual);
    }

    /**
     * @param string $service
     * @dataProvider providerInvalidLoggerService
     * @expectedException \Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    public function testInvalidLoggerService($service)
    {
        $actual = $this->serviceManager->get($service);
    }

    /**
     * @group 5254
     */
    public function testRetrievesDatabaseServiceFromServiceManagerWhenEncounteringDbWriter()
    {
        $db = $this->getMockBuilder('Zend\Db\Adapter\Adapter')
            ->disableOriginalConstructor()
            ->getMock();
        $serviceManager = new ServiceManager(new ServiceManagerConfig(array(
            'abstract_factories' => array('Zend\Log\LoggerAbstractServiceFactory'),
        )));
        $serviceManager->setService('Db\Logger', $db);
        $serviceManager->setService('Config', array(
            'log' => array(
                'Application\Log' => array(
                    'writers' => array(
                        array(
                            'name'     => 'db',
                            'priority' => 1,
                            'options'  => array(
                                'separator' => '_',
                                'column'    => array(),
                                'table'     => 'applicationlog',
                                'db'        => 'Db\Logger',
                            ),
                        ),
                    ),
                ),
            ),
        ));
        $logger = $serviceManager->get('Application\Log');
        $this->assertInstanceOf('Zend\Log\Logger', $logger);
        $writers = $logger->getWriters();
        $found   = false;
        foreach ($writers as $writer) {
            if ($writer instanceof DbWriter) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Did not find expected DB writer');
        $this->assertAttributeSame($db, 'db', $writer);
    }
}

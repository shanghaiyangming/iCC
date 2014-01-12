<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\EventManager\EventInterface;
use Zend\EventManager\GlobalEventManager;
use Zend\Mvc\View\Console\ViewManager;
use My\Common\Controller\Action;
use Zend\Code\Scanner\DirectoryScanner;
use Zend\Loader\StandardAutoloader;
use Zend\Code\Scanner\DocBlockScanner;

class IndexController extends Action
{

    private $_account;

    private $_resource;

    public function init()
    {
        $this->_account = $this->model(SYSTEM_ACCOUNT);
        $this->_resource = $this->model(SYSTEM_RESOURCE);
    }

    /**
     * IDatabase系统主控制面板
     *
     * @author young
     * @name ICC系统主控制面板
     * @version 2013.11.07 young
     */
    public function indexAction()
    {
        if ($this->_account->findOne(array(
            'username' => 'root'
        )) === null) {
            return $this->redirect()->toRoute('install');
        } else 
            if (! isset($_SESSION['account'])) {
                return $this->redirect()->toRoute('login');
            }
    }

    /**
     * 安装icc系统
     */
    public function installAction()
    {
        // 插入系统根用户
        if ($this->_account->findOne(array(
            'username' => 'admin'
        )) == null) {
            $datas = array();
            $this->_account->insert(array(
                'username' => 'root',
                'password' => sha1('yangming1983'),
                'role' => 'root',
                'isProfessional' => true,
                'expire' => new \MongoDate(strtotime('2020-12-31 23:59:59')),
                'active' => true
            ));
            $this->_account->insert(array(
                'username' => 'admin',
                'password' => sha1('yangming1983'),
                'role' => 'admin',
                'isProfessional' => true,
                'expire' => new \MongoDate(strtotime('2020-12-31 23:59:59')),
                'active' => true
            ));
        }
        
        // 获取全部方法列表到数据库中
        $this->addResource();
        
        return $this->redirect()->toRoute('home');
    }

    public function methodsAction()
    {
        $this->addResource();
        return $this->response;
    }

    /**
     * 检索指定目录下的全部资源到数据库中
     */
    private function addResource()
    {
        $this->_resource->remove(array());
        $scaner = new DirectoryScanner();
        $scaner->addDirectory(ROOT_PATH . '/module/Application/src/Application/Controller/');
        $scaner->addDirectory(ROOT_PATH . '/module/Idatabase/src/Idatabase/Controller/');
        foreach ($scaner->getClasses(true) as $classScanner) {
            $controllerName = $classScanner->getName();
            foreach ($classScanner->getMethods(true) as $method) {
                if ($this->endsWith($method->getName(), 'Action')) {
                    $actionName = $method->getName();
                    $docComment = $method->getDocComment();
                    $docBlockScanner = new DocBlockScanner($docComment);
                    $docAtName = $this->getDocNameValue($docBlockScanner->getTags());
                    
                    // 写入数据库
                    $this->_resource->insert(array(
                        'name' => $docAtName,
                        'alias' => $controllerName . '/' . $actionName,
                        'controller' => $controllerName,
                        'action' => $actionName
                    ));
                }
            }
        }
    }

    /**
     * 判断是否是Action
     *
     * @param string $haystack            
     * @param string $needle            
     * @return boolean
     */
    private function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return (substr($haystack, - $length) === $needle);
    }

    /**
     * 从tags中获取doc的@name属性的值，如果没有返回空
     *
     * @param array $tags            
     * @return string
     */
    private function getDocNameValue($tags)
    {
        if (! empty($tags)) {
            foreach ($tags as $tag) {
                if (trim($tag['name']) === '@name') {
                    return trim($tag['value']);
                }
            }
        }
        return '';
    }
}

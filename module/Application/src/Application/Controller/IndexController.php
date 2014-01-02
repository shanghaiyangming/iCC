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

class IndexController extends Action
{

    private $_account;

    public function init()
    {
        $this->_account = $this->model(SYSTEM_ACCOUNT);
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
            'username' => 'admin'
        )) == null) {
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
                'isProfessional' => 1,/*[common/professional]*/
                'expire' => new \MongoDate(strtotime('2020-12-31 23:59:59')),
                'active' => true
            ));
            $this->_account->insert(array(
                'username' => 'admin',
                'password' => sha1('yangming1983'),
                'role' => 'admin',
                'isProfessional' => 1,/*[common/professional]*/
                'expire' => new \MongoDate(strtotime('2020-12-31 23:59:59')),
                'active' => true
            ));
            
        }
        return $this->redirect()->toRoute('home');
    }
}

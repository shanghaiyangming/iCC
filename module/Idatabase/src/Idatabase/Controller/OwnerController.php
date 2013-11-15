<?php
/**
 * iDatabase项目用户所属组别管理
 *
 * @author young 
 * @version 2013.11.11
 * 
 */
namespace Idatabase\Controller;

use My\Common\ActionController;
use Zend\View\Model\ViewModel;
use Zend\EventManager\EventInterface;
use Zend\EventManager\GlobalEventManager;

class OwnerController extends BaseActionController
{

    private $_modelProject;

    public function init()
    {
        $this->_modelProject = $this->model(IDATABASE_PROJECTS);
    }

    /**
     * 读取全部项目列表
     *
     * @author young
     * @name 读取全部项目列表
     * @version 2013.11.07 young
     */
    public function indexAction()
    {
        $projectInfo = $this->_modelProject->findOne(array('_id'=>$this->project_id,'owner'=>));
    }

    public function insertAction()
    {}

    public function editAction()
    {}

    public function removeAction()
    {}

    /**
     * 权限分享，账户所属人员如果具备分享权限，可以将项目分享给别的用户
     */
    public function shareAction()
    {}
}
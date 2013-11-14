<?php
/**
 * iDatabase项目管理
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
use Zend\View\Model\JsonModel;

class ProjectController extends BaseActionController
{
    public $_project;
    
    public function init() {
        $this->_project = $this->model(IDATABASE_PROJECTS);
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
        $query = array();
        return $this->findAll(IDATABASE_PROJECTS,$query);
    }

    /**
     * 添加新的项目
     * 
     * @author young
     * @name 添加新的项目
     * @version 2013.11.14 young
     * @return JsonModel
     */
    public function addAction()
    {
        $project = array();
        
        $this->_project->insert($project);
        return $this->msg(true, 'OK');
    }
    
    /**
     * 编辑新的项目
     *
     * @author young
     * @name 编辑新的项目
     * @version 2013.11.14 young
     * @return JsonModel
     */
    public function editAction() {
        
    }
    
    /**
     * 删除新的项目
     *
     * @author young
     * @name 删除新的项目
     * @version 2013.11.14 young
     * @return JsonModel
     */
    public function removeAction() {
        
    }
    
    /**
     * 权限分享，账户所属人员如果具备分享权限，可以将项目分享给别的用户
     */
    public function shareAction() {
        
    }
}

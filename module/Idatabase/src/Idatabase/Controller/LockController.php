<?php
/**
 * iDatabase集合加密管理
 *
 * @author young 
 * @version 2014.01.04
 * 
 */
namespace Idatabase\Controller;

use My\Common\ActionController;
use Zend\View\Model\ViewModel;
use Zend\EventManager\EventInterface;
use Zend\EventManager\GlobalEventManager;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;

class LockController extends BaseActionController
{

    private $_lock;

    private $_project_id;

    private $_collection_id;

    public function init()
    {
        $this->_project_id = isset($_REQUEST['project_id']) ? trim($_REQUEST['project_id']) : '';
        if (empty($this->_project_id))
            throw new \Exception('$this->_project_id值未设定');
        
        $this->_collection_id = isset($_REQUEST['collection_id']) ? trim($_REQUEST['collection_id']) : '';
        if (empty($this->_collection_id))
            throw new \Exception('$this->_collection_id值未设定');
        
        $this->_lock = $this->model(IDATABASE_LOCK);
    }

    /**
     * 读取映射关系
     *
     * @author young
     * @name 读取映射关系
     * @version 2013.01.04 young
     */
    public function indexAction()
    {
        $query = array(
            'project_id' => $this->_project_id,
            'collection_id' => $this->_collection_id
        );
        return $this->findAll(IDATABASE_MAPPING, $query);
    }

    /**
     * 更新映射关系
     *
     * @author young
     * @name 更新映射关系
     * @version 2014.01.02 young
     * @return JsonModel
     */
    public function updateAction()
    {
        $oldPassword = trim($this->params()->fromPost('oldPassword', ''));
        $password = trim($this->params()->fromPost('password', ''));
        $repeatPassword = trim($this->params()->fromPost('repeatPassword', ''));
        $active = filter_var($this->params()->fromPost('active', ''), FILTER_VALIDATE_BOOLEAN);
        
        $criteria = array(
            'project_id' => $this->_project_id,
            'collection_id' => $this->_collection_id
        );
        
        $datas = array(
            'password' => sha1($password),
            'active' => $active
        );
        
        $rst = $this->_lock->update($criteria, array(
            '$set' => $datas
        ), array(
            'upsert' => true
        ));
        
        if ($rst['ok']) {
            return $this->msg(true, '设定集合访问密钥成功');
        } else {
            return $this->msg(false, Json::encode($rst));
        }
    }
}

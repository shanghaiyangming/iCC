<?php
/**
 * iDatabase密钥管理
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
use Zend\Json\Json;

class KeyController extends BaseActionController
{

    private $_project;

    private $_project_id;

    private $_keys;

    public function init()
    {
        $this->_project_id = isset($_REQUEST['project_id']) ? trim($_REQUEST['project_id']) : '';
        
        if (empty($this->_project_id))
            throw new \Exception('$this->_project_id值未设定');
        
        $this->_project = $this->model(IDATABASE_PROJECTS);
        if ($this->_project->count(array(
            '_id' => myMongoId($this->_project_id)
        )) == 0) {
            throw new \Exception('$this->_project_id无效');
        }
        
        $this->_keys = $this->model(IDATABASE_KEYS);
    }

    /**
     * 读取特定项目的密钥列表
     *
     * @author young
     * @name 读取特定项目的密钥列表
     * @version 2014.01.01 young
     */
    public function indexAction()
    {
        $query = array(
            'project_id' => $this->_project_id
        );
        return $this->findAll(IDATABASE_KEYS, $query);
    }

    /**
     * 添加新的密钥
     *
     * @author young
     * @name 添加新的密钥
     * @version 2013.11.14 young
     * @return JsonModel
     */
    public function addAction()
    {
        $name = $this->params()->fromPost('name', null);
        $desc = $this->params()->fromPost('desc', null);
        $key = $this->params()->fromPost('key', null);
        $expire = $this->params()->fromPost('expire', null);
        $active = filter_var($this->params()->fromPost('active', ''), FILTER_VALIDATE_BOOLEAN);
        $project_id = $this->params()->fromPost('project_id', null);
        
        if ($name == null) {
            return $this->msg(false, '请填写密钥名称');
        }
        
        if ($key == null) {
            return $this->msg(false, '请填写密钥');
        }
        
        if (strlen($key) < 8) {
            return $this->msg(false, '密钥长度不少于8位');
        }
        
        if ($desc == null) {
            return $this->msg(false, '请填写密钥描述');
        }
        
        if ($expire == null) {
            return $this->msg(false, '请设定密钥过期时间');
        }
        
        $expire = intval(strtotime($expire));
        if ($expire === 0) {
            return $this->msg(false, '无效的日期格式');
        }
        
        $datas = array();
        $datas['name'] = $name;
        $datas['desc'] = $desc;
        $datas['key'] = $key;
        $datas['expire'] = new \MongoDate($expire);
        $datas['active'] = $active;
        $datas['project_id'] = $project_id;
        $this->_keys->insert($datas);
        
        return $this->msg(true, '添加密钥成功');
    }

    /**
     * 编辑密钥
     *
     * @author young
     * @name 编辑密钥
     * @version 2013.11.14 young
     * @return JsonModel
     */
    public function editAction()
    {
        $_id = $this->params()->fromPost('_id', null);
        $name = $this->params()->fromPost('name', null);
        $desc = $this->params()->fromPost('desc', null);
        $key = $this->params()->fromPost('key', null);
        $expire = $this->params()->fromPost('expire', null);
        $active = filter_var($this->params()->fromPost('active', ''), FILTER_VALIDATE_BOOLEAN);
        $project_id = $this->params()->fromPost('project_id', null);
        
        if ($name == null) {
            return $this->msg(false, '请填写密钥名称');
        }
        
        if ($key == null) {
            return $this->msg(false, '请填写密钥');
        }
        
        if (strlen($key) < 8) {
            return $this->msg(false, '密钥长度不少于8位');
        }
        
        if ($desc == null) {
            return $this->msg(false, '请填写密钥描述');
        }
        
        if ($expire == null) {
            return $this->msg(false, '请设定密钥过期时间');
        }
        
        $expire = intval(strtotime($expire));
        if ($expire === 0) {
            return $this->msg(false, '无效的日期格式');
        }
        
        $datas = array();
        $datas['name'] = $name;
        $datas['desc'] = $desc;
        $datas['key'] = $key;
        $datas['expire'] = new \MongoDate($expire);
        $datas['active'] = $active;
        $datas['project_id'] = $project_id;
        $this->_keys->update(array(
            '_id' => myMongoId($_id)
        ), array(
            '$set' => $datas
        ));
        
        return $this->msg(true, '编辑信息成功');
    }

    /**
     * 删除密钥
     *
     * @author young
     * @name 删除密钥
     * @version 2013.11.14 young
     * @return JsonModel
     */
    public function removeAction()
    {
        $_id = $this->params()->fromPost('_id', null);
        try {
            $_id = Json::decode($_id, Json::TYPE_ARRAY);
        } catch (\Exception $e) {
            return $this->msg(false, '无效的json字符串');
        }
        
        if (! is_array($_id)) {
            return $this->msg(false, '请选择你要删除的项');
        }
        foreach ($_id as $row) {
            $this->_keys->remove(array(
                '_id' => myMongoId($row)
            ));
        }
        return $this->msg(true, '删除信息成功');
    }
}

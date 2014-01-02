<?php
/**
 * iDatabase集合映射管理系统
 *
 * @author young 
 * @version 2014.01.02
 * 
 */
namespace Idatabase\Controller;

use My\Common\ActionController;
use Zend\View\Model\ViewModel;
use Zend\EventManager\EventInterface;
use Zend\EventManager\GlobalEventManager;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;

class MappingController extends BaseActionController
{

    private $_mapping;

    private $_project_id;
    
    private $_collection_id;

    public function init()
    {
        $this->_project_id = isset($_REQUEST['project_id']) ? trim($_REQUEST['project_id']) : '';
        
        if (empty($this->_project_id))
            throw new \Exception('$this->_project_id值未设定');
        
        $this->_collection = $this->model(IDATABASE_COLLECTIONS);
        $this->_plugin_collection = $this->model(IDATABASE_PLUGINS_COLLECTIONS);
    }

    /**
     * 读取指定项目内的全部集合列表
     * 支持专家模式和普通模式显示，对于一些说明表和关系表，请在定义时，定义为普通模式
     *
     * @author young
     * @name 读取指定项目内的全部集合列表
     * @version 2013.11.19 young
     */
    public function indexAction()
    {
        $search = trim($this->params()->fromQuery('query', ''));
        $plugin_id = $this->params()->fromQuery('plugin_id', '');
        
        $sort = array(
            'orderBy' => 1,
            '_id' => - 1
        );
        
        $query = array(
            'plugin_id' => $plugin_id,
            'project_id' => $this->_project_id
        );
        
        if ($search != '') {
            $search = myMongoRegex($search);
            $query = array(
                '$and' => array(
                    $query,
                    array(
                        '$or' => array(
                            array(
                                'name' => $search
                            ),
                            array(
                                'alias' => $search
                            )
                        )
                    )
                )
            );
        }
        
        return $this->findAll(IDATABASE_COLLECTIONS, $query, $sort);
    }

    /**
     * 添加新的集合
     *
     * @author young
     * @name 添加新的集合
     * @version 2013.11.20 young
     * @return JsonModel
     */
    public function addAction()
    {
        
    }

    /**
     * 批量编辑集合信息
     *
     * @author young
     * @name 批量编辑集合信息
     * @version 2013.12.02 young
     * @return JsonModel
     */
    public function saveAction()
    {
        return $this->msg(true, '集合编辑不支持批量修改功能');
    }

}

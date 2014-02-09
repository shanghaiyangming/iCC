<?php
/**
 * iDatabase仪表盘控制，显示宏观统计视图
 *
 * @author young 
 * @version 2013.11.11
 * 
 */
namespace Idatabase\Controller;

use My\Common\Controller\Action;

class DashboardController extends Action
{

    private $_statistic;

    private $_project;
    
    private $_collection;
    
    private $_project_id;

    public function init()
    {
        $this->_project_id = isset($_REQUEST['__PROJECT_ID__']) ? trim($_REQUEST['__PROJECT_ID__']) : '';
    }

    /**
     * IDatabase仪表盘显示界面
     * 
     * @author young
     * @name IDatabase仪表盘显示界面
     * @version 2013.11.11 young
     */
    public function indexAction()
    {
        $query = array('project_id'=>$this->_project_id);
    	$cursor = $this->_collection->find($query);
    	while($cursor->hasNext()) {
    	    $row = $cursor->getNext();
    	    $collection_id = $row['_id']->__toString();
    	}
    }
}

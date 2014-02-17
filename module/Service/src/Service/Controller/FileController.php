<?php
/**
 * File下载处理函数
 *
 * @author young 
 * @version 2014.02.12
 * 
 */
namespace Service\Controller;

use My\Common\Controller\Action;

class FileController extends Action
{

    private $_file;

    public function init()
    {
        $this->_file = $this->model('Idatabase\Model\File');
    }

    public function indexAction()
    {
        $id = $this->params()->fromRouter('id', null);
        $download = $this->params()->fromRouter('download', false);
        $gridFsFile = $this->_file->getGridFsFileById($id);
        if($gridFsFile instanceof \MongoGridFSFile) {
            $this->output($gridFsFile,false);
            return $this->response;
        }
        else {
            header("HTTP/1.1 404 Not Found");
            return $this->response;
        }
    }


}


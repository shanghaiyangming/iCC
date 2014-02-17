<?php
namespace Idatabase\Model;

use My\Common\Model\Mongo;

class File extends Mongo
{

    protected $collection = IDATABASE_FILES;

    /**
     * 显示/下载资源信息
     *
     * @param MongoGridFSFile $gridFsFile            
     */
    private function output(\MongoGridFSFile $gridFsFile, $download = false)
    {
        setHeaderExpires();
        $fileInfo = $gridFsFile->file;
        $fileName = $fileInfo['filename'];
        if (isset($fileInfo['contentType'])) {
            header('Content-Type: ' . $fileInfo['contentType'] . ';');
        }
        
        if ($download)
            header('Content-Disposition:attachment;filename="' . $fileName . '"');
        else
            header('Content-Disposition:filename="' . $fileName . '"');
        
        $stream = $gridFsFile->getResource();
        while (! feof($stream)) {
            echo fread($stream, 8192);
        }
    }
}
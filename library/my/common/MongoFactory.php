<?php
namespace My\Common;

use Traversable;
use Zend\Stdlib\ArrayUtils;

/**
 * $cfg = array(
 * 'options'=>array('key'=>'value'),
 * 'servers'=>array(
 * 'default'=>array(
 * array('server','port')
 * ),
 * 'analyze'=>array(
 * array('server','port')
 * )
 * )
 * );
 * 
 * @author Young
 *        
 */
abstract class MongoFactory 
{
    public static function factory($cfg) {
        if ($cfg instanceof Traversable) {
            $cfg = ArrayUtils::iteratorToArray($cfg);
        }
        
        if (!is_array($cfg)) {
            throw new Exception\InvalidArgumentException('配置信息未设定');
        }
        
        if (!isset($cfg['cluster']) || empty($cfg['cluster'])) {
            throw new Exception\InvalidArgumentException('配置信息中缺少cluster参数');
        }
       
        $options = array();
        $options['connectTimeoutMS'] = 60000;
        $options['socketTimeoutMS'] = 60000;
        $options['w'] = 2;
        $options['wTimeout'] = 60000;
        
        if(isset($cfg['options']) && !empty($cfg['options'])) {
            $options = array_merge($options,$cfg['options']);
        }
        
        $cluster = array();
        foreach($cfg['cluster'] as $clusterName=>$clusterInfo) {
            try {
                shuffle($clusterInfo['servers']);
                $dnsString = 'mongodb://'.join(',', $clusterInfo['servers']);
                $connect = new MongoClient($dnsString,$options);
                $connect->setReadPreference(MongoClient::RP_PRIMARY_PREFERRED);
                $cluster[$clusterName]['connect'] = $connect;
            }
            catch (Exception $e) {
                throw new Exception('无法建立Mongodb连接');
            }
        
            foreach($clusterInfo['dbs'] as $db) {
                $cluster[$clusterName]['dbs'][$db] = $connect->selectDB($db);
            }
            unset($connect);
        }
        
        return $cluster;
    }
    

}







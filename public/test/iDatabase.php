<?php

/**
 * 
 * 客户端调用UMA iDatabase服务的php版本SDK
 * 
 * @version 2.2
 * @author Young
 * 
 * @change2.2
 * @date 2013-11-08
 * 1. 去掉异步方法
 * 2. 全部改写为静态方法
 * 3. 查询由多脚本多次查询，变更为单脚本单次查询
 * 
 * @change2.1 
 * @date 2013-05
 * 1. 修正了签名算法（之前是伪签名）
 * 2. 增加aggregation框架支持
 * 3. 取消findAndModify限制，支持全部参数
 * 4. 修正distinct理解错误
 * 5. find方法增加fields参数
 * 6. 增加find方法中2d坐标检索的支持
 * 7. 增加异步方法
 */
class iDatabase
{

    /**
     * soap服务的调用地址
     *
     * @var string
     */
    private static $_wsdl = 'http://scrm.umaman.com/soa/db2/soap?wsdl';

    /**
     * 是否每次加载WSDL 默认为false
     *
     * @var string
     */
    private static $_refresh = false;

    /**
     * 身份认证的命名空间
     *
     * @var string
     */
    private static $_namespace = 'http://scrm.umaman.com/soa/db2/soap';

    /**
     * 身份认证中的授权方法名
     *
     * @var string
     */
    private static $_authenticate = 'authenticate';

    /**
     * 项目编号
     *
     * @var string
     */
    private static $_project_id;

    /**
     * 项目签名密码
     *
     * @var string
     */
    private static $_password;

    /**
     * 随机字符串
     *
     * @var string
     */
    private static $_rand;

    /**
     * 调用客户端
     *
     * @var resource
     */
    private static $_client = null;

    /**
     * 是否开启debug功能
     *
     * @var bool
     */
    private static $_debug = false;

    /**
     * 记录错误信息
     *
     * @var string
     */
    private static $_error;

    /**
     * $dns = array('dbname'=>array(
     * 'project_id'=>xxxxxx,
     * 'password'=>yyyyyyy
     * ));
     *
     * @param string $project_id            
     * @param string $password            
     */
    public static function getInstance($dns)
    {
        if (self::$_client === null) {
            self::$_client = array();
            if (empty($dns)) {
                self::$_client = null;
            }
            foreach ($dns as $dbName => $config) {
                $auth = array();
                $auth['project_id'] = $config['project_id'];
                $auth['rand'] = $rand = time();
                $auth['sign'] = md5($config['project_id'] . $rand . $config['password']);
                $authenticate = new SoapHeader(self::$_namespace, self::$_authenticate, new SoapVar($auth, SOAP_ENC_OBJECT), false);
                
                $client = self::callSoap();
                $client->__setSoapHeaders(array(
                    $authenticate
                ));
                self::$_client[$dbName] = $client;
                unset($client);
            }
            return self::$_client;
        }
        return self::$_client;
    }

    /**
     * 开启或者关闭debug模式
     *
     * @param bool $debug            
     */
    public static function setDebug($debug = false)
    {
        self::$_debug = is_bool($debug) ? $debug : false;
    }

    /**
     * 开启或者关闭soap客户端的wsdl缓存
     *
     * @param bool $refresh            
     */
    public static function setRefresh($refresh = false)
    {
        self::$_refresh = is_bool($refresh) ? $refresh : false;
    }

    /**
     * 建立soap链接
     *
     * @param string $wsdl            
     * @param bool $refresh            
     * @return resource boolean
     */
    private static function callSoap()
    {
        try {
            $options = array(
                'soap_version' => SOAP_1_2, // 必须是1.2版本的soap协议，支持soapheader
                'exceptions' => true,
                'trace' => true,
                'connection_timeout' => 30, // 避免网络延迟导致的链接丢失
                'keep_alive' => true,
                'compression' => true, // 当为true时候，异步操作将无法正常工作
                'cache_wsdl' => WSDL_CACHE_DISK
            );
            
            return new SoapClient(self::$_wsdl, $options);
        } catch (Exception $e) {
            self::exceptionMsg($e);
            throw new iDatabseException(self::$_error);
        }
    }

    /**
     * 签名算法
     *
     * @return string
     */
    private static function sign()
    {
        return md5(self::$_project_id . self::$_rand . self::$_password);
    }

    /**
     * 格式化返回结果
     *
     * @param string $rst            
     * @return array
     */
    private static function rst($rst)
    {
        $rst = json_decode($rst, true);
        if (array_key_exists('err', $rst)) {
            throw new iDatabseException($rst['err'], $rst['code']);
        } else
            return $rst['result'];
    }

    /**
     * 编码
     *
     * @param array $array            
     * @return string boolean
     */
    private static function jsonEncode($array)
    {
        $json = json_encode($array);
        if (json_last_error() === JSON_ERROR_NONE)
            return $json;
        else {
            $errorMsg = "\nJSON ERROR:" . json_last_error() . "\n";
            self::$_error = $errorMsg;
            throw new iDatabseException(self::$_error);
        }
    }

    /**
     * 查询某个表中的数据
     *
     * @param string $form            
     * @param array $query            
     * @param array $sort            
     * @param int $skip            
     * @param int $limit            
     * @param array $fields            
     * @return array boolean
     */
    public static function find($dbName, $form, array $query, array $sort = null, $skip = 0, $limit = 10, Array $fields = array())
    {
        try {
            $rst = self::$_client[$dbName]->find($form, self::jsonEncode($query), self::jsonEncode($sort), $skip, $limit, self::jsonEncode($fields));
            return self::rst($rst);
        } catch (Exception $e) {
            self::exceptionMsg($e);
            throw new iDatabseException(self::$_error);
        }
    }

    /**
     * 查询某个表中的数据,并根据指定的key字段进行distinct唯一处理
     *
     * @param string $form            
     * @param string $key            
     * @param array $query            
     * @return array boolean
     */
    public static function distinct($dbName, $form, $key, array $query)
    {
        try {
            $rst = self::$_client[$dbName]->distinct($form, $key, self::jsonEncode($query));
            return self::rst($rst);
        } catch (Exception $e) {
            self::exceptionMsg($e);
            throw new iDatabseException(self::$_error);
        }
    }

    /**
     * 查询单条信息
     *
     * @param string $form            
     * @param array $query            
     * @return array boolean
     */
    public static function findOne($dbName, $form, array $query)
    {
        try {
            $rst = self::$_client[$dbName]->findOne($form, self::jsonEncode($query));
            return self::rst($rst);
        } catch (Exception $e) {
            self::exceptionMsg($e);
            throw new iDatabseException(self::$_error);
        }
    }

    /**
     * 获取某个集合的全部数据(注意，当集合数据过大的时候，SOAP结构体超过100M时候，可能导致失败)
     *
     * @param string $form            
     * @param array $query            
     * @param array $sort            
     * @param array $fields            
     * @return array boolean
     */
    public static function findAll($dbName, $form, array $query, array $sort = null, Array $fields = array())
    {
        try {
            if ($sort == null)
                $sort = array(
                    '$natural' => 1
                );
            $rst = self::$_client[$dbName]->findAll($form, self::jsonEncode($query), self::jsonEncode($sort), self::jsonEncode($fields));
            return self::rst($rst);
        } catch (Exception $e) {
            self::exceptionMsg($e);
            throw new iDatabseException(self::$_error);
        }
    }

    /**
     * 保存指定数据的数据
     * 如果不存在_id字段，则执行插入操作，插入指定$datas的数据
     * 如果包含_id则更新相应的数据，注意原有的数据将不被保留
     * 例如：
     * db.products.save( { _id: 100, item: "water", qty: 30 } ) --结果为--> { "_id"
     * : 100, "item" : "water", "qty" : 30 }
     * db.products.save( { _id: 100, item:"juice" } ) --结果为--> { "_id" : 100,
     * "item" : "juice" }
     * 所以使用该方法时，请注意上面用法上的区别
     *
     * @param string $form            
     * @param array $datas            
     * @return Array boolean
     */
    public static function save($dbName, $form, array $datas)
    {
        try {
            $rst = self::$_client[$dbName]->save($form, self::jsonEncode($datas));
            return self::rst($rst);
        } catch (Exception $e) {
            self::exceptionMsg($e);
            throw new iDatabseException(self::$_error);
        }
    }

    /**
     * 执行findAndModify操作
     *
     * @param string $form            
     * @param array $options            
     * @return array boolean
     */
    public static function findAndModify($dbName, $form, array $options)
    {
        try {
            $rst = self::$_client[$dbName]->findAndModify($form, self::jsonEncode($options));
            return self::rst($rst);
        } catch (Exception $e) {
            self::exceptionMsg($e);
            throw new iDatabseException(self::$_error);
        }
    }

    /**
     * 执行count操作
     *
     * @param string $form            
     * @param array $query            
     * @return array boolean
     */
    public static function count($dbName, $form, array $query)
    {
        try {
            $rst = self::$_client[$dbName]->count($form, self::jsonEncode($query));
            return self::rst($rst);
        } catch (Exception $e) {
            self::exceptionMsg($e);
            throw new iDatabseException(self::$_error);
        }
    }

    /**
     * 执行remove操作
     *
     * @param string $form            
     * @param array $query            
     * @return array boolean
     */
    public static function remove($dbName, $form, array $query)
    {
        try {
            $rst = self::$_client[$dbName]->remove($form, self::jsonEncode($query));
            return self::rst($rst);
        } catch (Exception $e) {
            self::exceptionMsg($e);
            throw new iDatabseException(self::$_error);
        }
    }

    /**
     * 执行insert操作
     *
     * @param string $form            
     * @param array $datas            
     * @return array boolean
     */
    public static function insert($dbName, $form, array $datas)
    {
        try {
            $rst = self::$_client[$dbName]->insert($form, self::jsonEncode($datas));
            return self::rst($rst);
        } catch (Exception $e) {
            self::exceptionMsg($e);
            throw new iDatabseException(self::$_error);
        }
    }

    /**
     * 执行批量更新操作
     *
     * @param string $form            
     * @param array $criteria            
     * @param array $object            
     * @return array boolean
     */
    public static function update($dbName, $form, array $criteria, array $object)
    {
        try {
            $rst = self::$_client[$dbName]->update($form, self::jsonEncode($criteria), self::jsonEncode($object));
            return self::rst($rst);
        } catch (Exception $e) {
            self::exceptionMsg($e);
            throw new iDatabseException(self::$_error);
        }
    }

    /**
     * aggregate框架操作
     *
     * @param string $form            
     * @param array $ops            
     * @return array boolean
     */
    public static function aggregate($dbName, $form, array $ops)
    {
        try {
            $rst = self::$_client[$dbName]->aggregate($form, self::jsonEncode($ops));
            return self::rst($rst);
        } catch (Exception $e) {
            self::exceptionMsg($e);
            throw new iDatabseException(self::$_error);
        }
    }

    /**
     * 将异常信息记录到$this->_error中
     *
     * @param object $e            
     * @return null
     */
    private static function exceptionMsg($e)
    {
        self::$_error = $e->getMessage() . $e->getFile() . $e->getLine() . $e->getTraceAsString();
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        if (self::$_debug) {}
    }
}

/**
 * iDatabase异常处理函数
 *
 * @author young
 *        
 */
class iDatabseException extends Exception
{
}


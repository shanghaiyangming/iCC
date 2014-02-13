<?php

class iDatabase
{

    /**
     * soap服务的调用地址
     *
     * @var string
     */
    private $_wsdl = 'http://localhost/service/database/index?wsdl';

    /**
     * 是否每次加载WSDL 默认为false
     *
     * @var string
     */
    private $_refresh = false;

    /**
     * 身份认证的命名空间
     *
     * @var string
     */
    private $_namespace = 'http://localhost/service/database/index';

    /**
     * 身份认证中的授权方法名
     *
     * @var string
     */
    private $_authenticate = 'authenticate';

    /**
     * 设定集合
     *
     * @var string
     */
    private $_set_collection = 'setCollection';

    /**
     * 项目编号
     *
     * @var string
     */
    private $_project_id;

    /**
     * 集合别名
     *
     * @var string
     */
    private $_collection_alias;

    /**
     * 密钥
     *
     * @var string
     */
    private $_password;

    /**
     * 随机字符串
     *
     * @var string
     */
    private $_rand;

    /**
     * 密钥编号，为空时候，使用默认密钥
     *
     * @var string
     */
    private $_key_id = '';

    /**
     * 调用客户端
     *
     * @var resource
     */
    private $_client;

    /**
     * 是否开启debug功能
     *
     * @var bool
     */
    private $_debug = false;

    /**
     * 记录错误信息
     *
     * @var string
     */
    private $_error;

    /**
     *
     * @param string $project_id            
     * @param string $collectionAlias            
     * @param string $password            
     * @param string $key_id            
     */
    public function __construct($project_id, $collectionAlias, $password, $key_id = '')
    {
        $this->_project_id = $project_id;
        $this->_collection_alias = $collectionAlias;
        $this->_password = $password;
        $this->_rand = sha1(time());
        $this->_key_id = $key_id;
        $this->connect();
    }

    /**
     * 开启或者关闭debug模式
     *
     * @param bool $debug            
     */
    public function setDebug($debug = false)
    {
        $this->_debug = is_bool($debug) ? $debug : false;
    }

    /**
     * 开启或者关闭soap客户端的wsdl缓存
     *
     * @param bool $refresh            
     */
    public function setRefresh($refresh = false)
    {
        $this->_refresh = is_bool($refresh) ? $refresh : false;
    }

    /**
     * 建立soap链接
     *
     * @param string $wsdl            
     * @param bool $refresh            
     * @return resource boolean
     */
    private function callSoap($wsdl, $refresh = false)
    {
        try {
            $options = array(
                'soap_version' => SOAP_1_2, // 必须是1.2版本的soap协议，支持soapheader
                'SoapFaults' => true,
                'trace' => true,
                'connection_timeout' => 300, // 避免网络延迟导致的链接丢失
                'keep_alive' => true,
                'compression' => true
            );
            
            $this->_client = new SoapClient($wsdl, $options);
            return $this->_client;
        } catch (SoapFault $e) {
            $this->SoapFaultMsg($e);
            return false;
        }
    }

    /**
     * 进行调用授权身份认证处理
     *
     * @return resource
     */
    private function connect()
    {
        $auth = array();
        $auth['project_id'] = $this->_project_id;
        $auth['rand'] = $this->_rand;
        $auth['sign'] = $this->sign();
        $auth['key_id'] = $this->_key_id;
        $authenticate = new SoapHeader($this->_namespace, $this->_authenticate, new SoapVar($auth, SOAP_ENC_OBJECT), false);
        
        $alias = array();
        $alias['collectionAlias'] = $this->_collection_alias;
        $setCollection = new SoapHeader($this->_namespace, $this->_set_collection, new SoapVar($alias, SOAP_ENC_OBJECT), false);
        $this->_client = $this->callSoap($this->_wsdl, $this->_refresh);
        $this->_client->__setSoapHeaders(array(
            $authenticate,
            $setCollection
        ));
        return $this->_client;
    }

    /**
     * 签名算法
     *
     * @return string
     */
    private function sign()
    {
        return md5($this->_project_id . $this->_rand . $this->_password);
    }

    /**
     * 格式化返回结果
     *
     * @param string $rst            
     * @return array
     */
    private function rst($rst)
    {
        $rst = json_decode($rst, true);
        if (array_key_exists('err', $rst))
            return $rst;
        else
            return $rst['result'];
    }

    /**
     * 执行count操作
     *
     * @param array $query            
     * @return array boolean
     */
    public function count(array $query)
    {
        try {
            $rst = $this->_client->count(serialize($query));
            return $this->rst($rst);
        } catch (SoapFault $e) {
            $this->SoapFaultMsg($e);
            return false;
        }
    }

    /**
     * 查询某个表中的数据,并根据指定的key字段进行distinct唯一处理
     *
     * @param string $key            
     * @param array $query            
     * @return array boolean
     */
    public function distinct($key, array $query)
    {
        try {
            $rst = $this->_client->distinct($key, serialize($query));
            return $this->rst($rst);
        } catch (SoapFault $e) {
            $this->SoapFaultMsg($e);
            return false;
        }
    }

    /**
     * 查询某个表中的数据
     *
     * @param array $query            
     * @param array $sort            
     * @param int $skip            
     * @param int $limit            
     * @param array $fields            
     * @return array boolean
     */
    public function find(array $query, array $sort = null, $skip = 0, $limit = 10, Array $fields = array())
    {
        try {
            $rst = $this->_client->find(serialize($query), serialize($sort), $skip, $limit, serialize($fields));
            return $this->rst($rst);
        } catch (SoapFault $e) {
            $this->SoapFaultMsg($e);
            return false;
        }
    }

    /**
     * 查询单条信息
     *
     * @param array $query            
     * @return array boolean
     */
    public function findOne(array $query)
    {
        try {
            $rst = $this->_client->findOne(serialize($query));
            return $this->rst($rst);
        } catch (SoapFault $e) {
            $this->SoapFaultMsg($e);
            return false;
        }
    }


    /**
     * 查询全部信息
     * @param array $query
     * @param array $sort
     * @param array $fields
     * @return array
     */
    public function findAll(array $query, array $sort = array('_id'=>-1), array $fields = array())
    {
        try {
            $rst = $this->_client->findAll(serialize($query), serialize($sort), serialize($fields));
            return $this->rst($rst);
        } catch (SoapFault $e) {
            $this->SoapFaultMsg($e);
            return false;
        }
    }

    /**
     * 执行findAndModify操作
     *
     * @param array $options            
     * @return array boolean
     */
    public function findAndModify(array $options)
    {
        try {
            $rst = $this->_client->findAndModify(serialize($options));
            return $this->rst($rst);
        } catch (SoapFault $e) {
            $this->SoapFaultMsg($e);
            return false;
        }
    }

    /**
     * 执行remove操作
     *
     * @param array $query            
     * @return array boolean
     */
    public function remove(array $query)
    {
        try {
            $rst = $this->_client->remove(serialize($query));
            return $this->rst($rst);
        } catch (SoapFault $e) {
            $this->SoapFaultMsg($e);
            return false;
        }
    }

    /**
     * 执行insert操作
     *
     * @param array $datas            
     * @return array boolean
     */
    public function insert(array $datas)
    {
        try {
            $rst = $this->_client->insert(serialize($datas));
            return $this->rst($rst);
        } catch (SoapFault $e) {
            $this->SoapFaultMsg($e);
            return false;
        }
    }

    /**
     * 执行批量更新操作
     *
     * @param array $criteria            
     * @param array $object            
     * @return array boolean
     */
    public function update(array $criteria, array $object)
    {
        try {
            $rst = $this->_client->update(serialize($criteria), serialize($object));
            return $this->rst($rst);
        } catch (SoapFault $e) {
            $this->SoapFaultMsg($e);
            return false;
        }
    }

    /**
     * aggregate框架操作
     *
     * @param array $ops            
     * @return array boolean
     */
    public function aggregate(array $ops)
    {
        try {
            $rst = $this->_client->aggregate(serialize($ops));
            return $this->rst($rst);
        } catch (SoapFault $e) {
            $this->SoapFaultMsg($e);
            return false;
        }
    }

    /**
     * 将异常信息记录到$this->_error中
     *
     * @param object $e            
     * @return null
     */
    private function SoapFaultMsg($e)
    {
        $this->_error = $e->getMessage() . $e->getFile() . $e->getLine() . $e->getTraceAsString();
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        if ($this->_debug) {
            var_dump($this->_error, $this->_client->__getLastRequestHeaders(), $this->_client->__getLastRequest(), $this->_client->__getLastResponseHeaders(), $this->_client->__getLastResponse());
        }
    }
}
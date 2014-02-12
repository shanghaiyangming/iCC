<?php

class iDatabase
{

    /**
     * soap服务的调用地址
     *
     * @var string
     */
    private $_wsdl = 'http://cloud.umaman.com/service/database/index?wsdl';

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
    private $_namespace = 'http://cloud.umaman.com/service/database/index';

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
    private $_collection_alias = 'setCollection';

    /**
     * 项目编号
     *
     * @var string
     */
    private $_project_id;

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
     * @param string $password            
     * @param string $key_id            
     */
    public function __construct($project_id, $password, $key_id = '')
    {
        $this->_project_id = $project_id;
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
                'exceptions' => true,
                'trace' => $this->_debug,
                'connection_timeout' => 300, // 避免网络延迟导致的链接丢失
                'keep_alive' => true,
                'compression' => true
            );
            if ($refresh == true)
                $options['cache_wsdl'] = WSDL_CACHE_NONE;
            else
                $options['cache_wsdl'] = WSDL_CACHE_BOTH;
            
            $this->_client = new MySoapClient($wsdl, $options);
            return $this->_client;
        } catch (Exception $e) {
            $this->exceptionMsg($e);
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
        $alias['collectionAlias'] = '';
        $setCollection = new SoapHeader($this->_namespace, $this->_collection_alias, new SoapVar($alias, SOAP_ENC_OBJECT), false);
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
    public function find($form, array $query, array $sort = null, $skip = 0, $limit = 10, Array $fields = array())
    {
        try {
            $rst = $this->_client->find($form, json_encode($query), json_encode($sort), $skip, $limit, json_encode($fields));
            return $this->rst($rst);
        } catch (Exception $e) {
            $this->exceptionMsg($e);
            return false;
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
    public function distinct($form, $key, array $query)
    {
        try {
            $rst = $this->_client->distinct($form, $key, json_encode($query));
            return $this->rst($rst);
        } catch (Exception $e) {
            $this->exceptionMsg($e);
            return false;
        }
    }

    /**
     * 查询单条信息
     *
     * @param string $form            
     * @param array $query            
     * @return array boolean
     */
    public function findOne($form, array $query)
    {
        try {
            $rst = $this->_client->findOne($form, json_encode($query));
            return $this->rst($rst);
        } catch (Exception $e) {
            $this->exceptionMsg($e);
            return false;
        }
    }

    /**
     * 执行findAndModify操作
     *
     * @param string $form            
     * @param array $options            
     * @return array boolean
     */
    public function findAndModify($form, array $options)
    {
        try {
            $rst = $this->_client->findAndModify($form, json_encode($options));
            return $this->rst($rst);
        } catch (Exception $e) {
            $this->exceptionMsg($e);
            return false;
        }
    }

    /**
     * 执行count操作
     *
     * @param string $form            
     * @param array $query            
     * @return array boolean
     */
    public function count($form, array $query)
    {
        try {
            $rst = $this->_client->count($form, json_encode($query));
            return $this->rst($rst);
        } catch (Exception $e) {
            $this->exceptionMsg($e);
            return false;
        }
    }

    /**
     * 执行remove操作
     *
     * @param string $form            
     * @param array $query            
     * @return array boolean
     */
    public function remove($form, array $query)
    {
        try {
            $rst = $this->_client->remove($form, json_encode($query));
            return $this->rst($rst);
        } catch (Exception $e) {
            $this->exceptionMsg($e);
            return false;
        }
    }

    /**
     * 执行insert操作
     *
     * @param string $form            
     * @param array $datas            
     * @return array boolean
     */
    public function insert($form, array $datas)
    {
        try {
            $rst = $this->_client->insert($form, json_encode($datas));
            return $this->rst($rst);
        } catch (Exception $e) {
            $this->exceptionMsg($e);
            return false;
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
    public function update($form, array $criteria, array $object)
    {
        try {
            $rst = $this->_client->update($form, json_encode($criteria), json_encode($object));
            return $this->rst($rst);
        } catch (Exception $e) {
            $this->exceptionMsg($e);
            return false;
        }
    }

    /**
     * aggregate框架操作
     *
     * @param string $form            
     * @param array $ops            
     * @return array boolean
     */
    public function aggregate($form, array $ops)
    {
        try {
            $rst = $this->_client->aggregate($form, json_encode($ops));
            return $this->rst($rst);
        } catch (Exception $e) {
            $this->exceptionMsg($e);
            return false;
        }
    }

    /**
     * 将异常信息记录到$this->_error中
     *
     * @param object $e            
     * @return null
     */
    private function exceptionMsg($e)
    {
        $this->_error = $e->getMessage() . $e->getFile() . $e->getLine() . $e->getTraceAsString();
    }

    public function __call($funcname, $arguments = Array())
    {
        try {
            $encodeArg = array();
            if (is_array($arguments)) {
                foreach ($arguments as $argument) {
                    if (is_array($argument)) {
                        $encodeArg[] = json_encode($argument);
                    } else {
                        $encodeArg[] = $argument;
                    }
                }
            }
            return call_user_func_array(array(
                $this->_client,
                $funcname
            ), $encodeArg);
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
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

/**
 * 扩展SOAP客户端增加异步处理模式
 */
class MySoapClient extends SoapClient
{

    public $asyncFunctionName = null;

    protected $_asynchronous = false;

    protected $_asyncResult = null;

    protected $_asyncAction = null;

    public function __call($functionName, $arguments)
    {
        if ($this->_asyncResult == null) {
            $this->_asynchronous = false;
            $this->_asyncAction = null;
            
            if (preg_match('/Async$/', $functionName) == 1) {
                $this->_asynchronous = true;
                $functionName = str_replace('Async', '', $functionName);
                $this->asyncFunctionName = $functionName;
            }
        }
        
        try {
            $result = @parent::__call($functionName, $arguments);
        } catch (SoapFault $e) {
            throw new Exception('There was an error querying the API: ' . $e->getMessage());
        }
        
        if ($this->_asynchronous == true) {
            return $this->_asyncAction;
        }
        return $result;
    }

    public function __doRequest($request, $location, $action, $version, $one_way = false)
    {
        if ($this->_asyncResult != null) {
            $result = $this->_asyncResult;
            unset($this->_asyncResult);
            return $result;
        }
        
        if ($this->_asynchronous == false) {
            $result = parent::__doRequest($request, $location, $action, $version, $one_way);
            return $result;
        } else {
            $this->_asyncAction = new SoapClientAsync($this, $this->asyncFunctionName, $request, $location, $action);
            return '';
        }
    }

    public function handleAsyncResult($functionName, $result)
    {
        $this->_asynchronous = false;
        $this->_asyncResult = $result;
        return $this->__call($functionName, array());
    }
}

class SoapClientAsync
{

    /**
     * 获取当前soapclient对象
     */
    protected $_soapClient;

    /**
     * 被叫方法名
     *
     * @var string
     */
    protected $_functionName;

    /**
     * 连接SOAP客户端的socket资源
     *
     * @var resource
     */
    protected $_socket;

    public function __construct($soapClient, $functionName, $request, $location, $action)
    {
        preg_match('%^(http(?:s)?)://(.*?)(/.*?)$%', $location, $matches);
        
        $this->_soapClient = $soapClient;
        $this->_functionName = $functionName;
        
        $protocol = $matches[1];
        $host = $matches[2];
        $endpoint = $matches[3];
        
        $headers = array(
            'POST ' . $endpoint . ' HTTP/1.1',
            'Host: ' . $host,
            'User-Agent: PHP-SOAP/' . phpversion(),
            'Content-Type: text/xml; charset=utf-8',
            'SOAPAction: "' . $action . '"',
            'Content-Length: ' . strlen($request),
            'Connection: close'
        );
        
        if ($protocol == 'https') {
            $host = 'ssl://' . $host;
            $port = 443;
        } else {
            $port = 80;
        }
        
        $data = implode("\r\n", $headers) . "\r\n\r\n" . $request . "\r\n";
        $this->_socket = fsockopen($host, $port, $errorNumber, $errorMessage);
        stream_set_blocking($this->_socket, 0);
        
        if ($this->_socket === false) {
            $this->_socket = null;
            throw new Exception('Unable to make an asynchronous API call: ' . $errorNumber . ': ' . $errorMessage);
        }
        
        if (fwrite($this->_socket, $data) === false) {
            throw new Exception('Unable to write data to an asynchronous API call.');
        }
    }

    public function wait()
    {
        $soapResult = '';
        
        while (! feof($this->_socket)) {
            $soapResult .= fread($this->_socket, 8192);
        }
        
        list ($headers, $data) = explode("\r\n\r\n", $soapResult);
        
        return $this->rst($this->_soapClient->handleAsyncResult($this->_functionName, $data));
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

    public function __destruct()
    {
        if ($this->_socket != null) {
            fclose($this->_socket);
        }
    }
}
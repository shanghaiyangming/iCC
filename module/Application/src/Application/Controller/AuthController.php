<?php
/**
 * 身份认证控制器
 *
 * @link https://github.com/Gregwar/Captcha 生成验证码的出处
 * 
 */
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\EventManager\EventInterface;
use Zend\EventManager\GlobalEventManager;
use Gregwar\Captcha\CaptchaBuilder;
use Application\Model\Auth;

class AuthController extends AbstractActionController
{

    private $_model;

    private $_mongos;

    /**
     * (non-PHPdoc)
     *
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        try {
            $this->_mongos = $this->getServiceLocator()->get('mongos');
            $this->_model = new Auth($this->_mongos);
            if ($this->_model instanceof \MongoCollection)
                echo '$this->_model instanceof \MongoCollection';
            else
                echo 'error';
            var_dump($this->_model->insert(array(
                'a' => time()
            )));
            var_dump($this->_model->findOne());
            echo 'OK';
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
        return $this->response;
    }

    public function loginAction()
    {}

    public function logoutAction()
    {}

    /**
     * 登录验证码生成
     *
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function captchaAction()
    {
        $builder = new CaptchaBuilder();
        // $builder->setBackgroundColor($r, $g, $b);
        // $builder->build($width = 150, $height = 40);
        $builder->build(150, 40);
        $_SESSION['phrase'] = $builder->getPhrase();
        // $builder->output($quality = 80);
        header('Content-type: image/jpeg');
        $this->response->setContent($builder->output(80));
        return $this->response;
    }
}

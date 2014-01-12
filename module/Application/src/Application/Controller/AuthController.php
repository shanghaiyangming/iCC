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
use Zend\Authentication\AuthenticationService;
use My\Common\Controller\Action;

class AuthController extends Action
{

    private $_account;

    /**
     * 显示登录页面
     *
     * @author young
     * @name 显示登录页面
     * @version 2013.11.07 young
     */
    public function indexAction()
    {
        $failure = $this->params()->fromRoute('failure', false);
        $view = new ViewModel(array(
            'failure' => $failure
        ));
        $view->setTerminal(true);
        return $view;
    }

    /**
     * 处理登录请求
     *
     * @author young
     * @name 处理登录请求
     * @version 2013.11.07 young
     */
    public function loginAction()
    {
        $this->_account = $this->model(SYSTEM_ACCOUNT);
        $username = $this->params()->fromPost('username', null);
        $password = $this->params()->fromPost('password', null);
        
        $accountInfo = $this->_account->findOne(array(
            'username' => $username,
            'password' => sha1($password),
            'expire' => array(
                '$gt' => new \MongoDate()
            )
        ));
        
        if ($accountInfo == null) {
            return $this->redirect()->toRoute('login', array(
                'failure' => true
            ));
        }
        
        $_SESSION['account'] = $accountInfo;
        return $this->redirect()->toRoute('home');
    }

    /**
     * 处理注销请求
     *
     * @author young
     * @name 处理注销请求
     * @version 2013.11.07 young
     */
    public function logoutAction()
    {
        session_destroy();
        $this->redirect()->toRoute('login');
    }

    /**
     * 保持登录状态
     */
    public function keepAction()
    {
    	
    }

    /**
     * 生成登录页面的图形验证码
     *
     * @author young
     * @name 生成登录页面的图形验证码
     * @version 2013.11.07 young
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function captchaAction()
    {
        $builder = new CaptchaBuilder();
        $builder->setBackgroundColor(255, 255, 255);
        $builder->setTextColor(255, 0, 255);
        $builder->setPhrase(rand(100000, 999999));
        $_SESSION['phrase'] = $builder->getPhrase();
        $builder->build(150, 40);
        header('Content-type: image/jpeg');
        $this->response->setContent($builder->output(80));
        return $this->response;
    }
}

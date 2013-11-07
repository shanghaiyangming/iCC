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

class AuthController extends AbstractActionController
{

    /**
     * @name 处理登录请求
     * @desc 
     */
    public function loginAction()
    {}

    /**
     * @name 处理注销请求
     * @desc 
     */
    public function logoutAction()
    {}

    /**
     * @name 生成登录页面的图形验证码
     * @desc 
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function captchaAction()
    {
        $builder = new CaptchaBuilder();
        $builder->build(150, 40);
        $_SESSION['phrase'] = $builder->getPhrase();
        header('Content-type: image/jpeg');
        $this->response->setContent($builder->output(80));
        return $this->response;
    }
}

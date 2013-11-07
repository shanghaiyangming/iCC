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
     * 显示登录页面
     * @author young
     * @name 显示登录页面
     * @version 2013.11.07 young
     */
    public function indexAction()
    {
        return $this->response;
    }

    /**
     * 处理登录请求
     * @author young
     * @name 处理登录请求
     * @version 2013.11.07 young
     */
    public function loginAction()
    {}

    /**
     * 处理注销请求
     * @author young
     * @name 处理注销请求
     * @version 2013.11.07 young
     */
    public function logoutAction()
    {}

    /**
     * 生成登录页面的图形验证码
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
        //$builder->setTextColor(68, 134, 246);
        $builder->setPhrase(rand(100000,999999));
        $_SESSION['phrase'] = $builder->getPhrase();
        $builder->build(150, 40);
        header('Content-type: image/jpeg');
        $this->response->setContent($builder->output(80));
        return $this->response;
    }
}

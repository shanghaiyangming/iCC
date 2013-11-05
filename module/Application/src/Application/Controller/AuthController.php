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
     * (non-PHPdoc)
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        
    }
    
    public function loginAction() {
        
    }
    
    public function logoutAction() {
        
    }

    /**
     * 登录验证码生成
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function captchaAction()
    {
        $builder = new CaptchaBuilder;
        //$builder->setBackgroundColor($r, $g, $b);
        //$builder->build($width = 150, $height = 40);
        $builder->build(150, 40);
        $_SESSION['phrase'] = $builder->getPhrase();
        //$builder->output($quality = 80);
        header('Content-type: image/jpeg');
        $this->response->setContent($builder->output(80));
        return $this->response;
    }
}
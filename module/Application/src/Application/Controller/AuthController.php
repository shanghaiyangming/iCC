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

    private $_model;

    private $_mongos;

    /**
     * (non-PHPdoc)
     *
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */

}

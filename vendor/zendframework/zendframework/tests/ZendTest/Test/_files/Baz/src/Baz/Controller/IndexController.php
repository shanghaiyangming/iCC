<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Baz\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    public function unittestsAction()
    {
        $this->getResponse()->getHeaders()->addHeaderLine('Content-Type: text/html');

        $num_get = $this->getRequest()->getQuery()->get('num_get', 0);
        $num_post = $this->getRequest()->getPost()->get('num_post', 0);

        return array('num_get' => $num_get, 'num_post' => $num_post);
    }

    public function consoleAction()
    {
        return 'foo, bar';
    }

    public function redirectAction()
    {
        return $this->redirect()->toUrl('http://www.zend.com');
    }

    public function exceptionAction()
    {
        throw new \RuntimeException('Foo error !');
    }
}

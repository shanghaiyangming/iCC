<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Filter;

use Zend\Filter\BaseName as BaseNameFilter;
use Zend\Stdlib\ErrorHandler;

/**
 * @group      Zend_Filter
 */
class BaseNameTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Ensures that the filter follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $filter = new BaseNameFilter();
        $valuesExpected = array(
            '/path/to/filename'     => 'filename',
            '/path/to/filename.ext' => 'filename.ext'
            );
        foreach ($valuesExpected as $input => $output) {
            $this->assertEquals($output, $filter($input));
        }
    }

    /**
     * Ensures that a warning is raised if array is used
     *
     * @return void
     */
    public function testWarningIsRaisedIfArrayUsed()
    {
        $filter = new BaseNameFilter();
        $input = array('/path/to/filename', '/path/to/filename.ext');

        ErrorHandler::start(E_USER_WARNING);
        $filtered = $filter->filter($input);
        $err = ErrorHandler::stop();

        $this->assertEquals($input, $filtered);
        $this->assertInstanceOf('ErrorException', $err);
        $this->assertContains('cannot filter', $err->getMessage());
    }

    /**
     * @return void
     */
    public function testReturnsNullIfNullIsUsed()
    {
        $filter   = new BaseNameFilter();
        $filtered = $filter->filter(null);
        $this->assertNull($filtered);
    }
}

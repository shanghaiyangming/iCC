<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Filter;

use Zend\Filter\Digits as DigitsFilter;
use Zend\Stdlib\ErrorHandler;

/**
 * @group      Zend_Filter
 */
class DigitsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Is PCRE is compiled with UTF-8 and Unicode support
     *
     * @var mixed
     **/
    protected static $_unicodeEnabled;

    /**
     * Creates a new Zend_Filter_Digits object for each test method
     *
     * @return void
     */
    public function setUp()
    {
        if (null === static::$_unicodeEnabled) {
            static::$_unicodeEnabled = (@preg_match('/\pL/u', 'a')) ? true : false;
        }
    }

    /**
     * Ensures that the filter follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $filter = new DigitsFilter();

        if (static::$_unicodeEnabled && extension_loaded('mbstring')) {
            // Filter for the value with mbstring
            /**
             * The first element of $valuesExpected contains multibyte digit characters.
             *   But , Zend_Filter_Digits is expected to return only singlebyte digits.
             *
             * The second contains multibyte or singebyte space, and also alphabet.
             * The third  contains various multibyte characters.
             * The last contains only singlebyte digits.
             */
            $valuesExpected = array(
                '1９2八3四８'     => '123',
                'Ｃ 4.5B　6'      => '456',
                '9壱8＠7．6，5＃4' => '987654',
                '789'              => '789'
                );
        } else {
            // POSIX named classes are not supported, use alternative 0-9 match
            // Or filter for the value without mbstring
            $valuesExpected = array(
                'abc123'  => '123',
                'abc 123' => '123',
                'abcxyz'  => '',
                'AZ@#4.3' => '43',
                '1.23'    => '123',
                '0x9f'    => '09'
                );
        }

        foreach ($valuesExpected as $input => $output) {
            $this->assertEquals(
                $output,
                $result = $filter($input),
                "Expected '$input' to filter to '$output', but received '$result' instead"
                );
        }
    }

    /**
     * Ensures that an error is raised if array is used
     *
     * @return void
     */
    public function testWarningIsRaisedIfArrayUsed()
    {
        $filter = new DigitsFilter();
        $input = array('abc123', 'abc 123');

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
        $filter   = new DigitsFilter();
        $filtered = $filter->filter(null);
        $this->assertNull($filtered);
    }
}

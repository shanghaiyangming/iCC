<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Form\View\Helper;

use Zend\Form\Element;
use Zend\Form\View\Helper\FormElementErrors as FormElementErrorsHelper;

class FormElementErrorsTest extends CommonTestCase
{
    public function setUp()
    {
        $this->helper = new FormElementErrorsHelper();
        parent::setUp();
    }

    public function getMessageList()
    {
        return array(
            'First error message',
            'Second error message',
            'Third error message',
        );
    }

    public function testLackOfMessagesResultsInEmptyMarkup()
    {
        $element = new Element('foo');
        $markup  = $this->helper->render($element);
        $this->assertEquals('', $markup);
    }

    public function testRendersErrorMessagesUsingUnorderedListByDefault()
    {
        $messages = $this->getMessageList();
        $element  = new Element('foo');
        $element->setMessages($messages);

        $markup = $this->helper->render($element);
        $this->assertRegexp('#<ul>\s*<li>First error message</li>\s*<li>Second error message</li>\s*<li>Third error message</li>\s*</ul>#s', $markup);
    }

    public function testCanSpecifyAttributesForOpeningTag()
    {
        $messages = $this->getMessageList();
        $element  = new Element('foo');
        $element->setMessages($messages);

        $markup = $this->helper->render($element, array('class' => 'error'));
        $this->assertContains('ul class="error"', $markup);
    }

    public function testCanSpecifyAttributesForOpeningTagUsingInvoke()
    {
        $helper = $this->helper;
        $messages = $this->getMessageList();
        $element  = new Element('foo');
        $element->setMessages($messages);

        $markup = $helper($element, array('class' => 'error'));
        $this->assertContains('ul class="error"', $markup);
    }

    public function testCanSpecifyAlternateMarkupStringsViaSetters()
    {
        $messages = $this->getMessageList();
        $element  = new Element('foo');
        $element->setMessages($messages);

        $this->helper->setMessageOpenFormat('<div%s><span>')
                     ->setMessageCloseString('</span></div>')
                     ->setMessageSeparatorString('</span><span>')
                     ->setAttributes(array('class' => 'error'));

        $markup = $this->helper->render($element);
        $this->assertRegexp('#<div class="error">\s*<span>First error message</span>\s*<span>Second error message</span>\s*<span>Third error message</span>\s*</div>#s', $markup);
    }

    public function testSpecifiedAttributesOverrideDefaults()
    {
        $messages = $this->getMessageList();
        $element  = new Element('foo');
        $element->setMessages($messages);
        $element->setAttributes(array('class' => 'foo'));

        $markup = $this->helper->render($element, array('class' => 'error'));
        $this->assertContains('ul class="error"', $markup);
    }

    public function testGetAttributes()
    {
        $messages = $this->getMessageList();
        $element  = new Element('foo');
        $element->setMessages($messages);

        $this->helper->setAttributes(array('class' => 'error'));

        $this->helper->render($element);

        $this->assertEquals(array('class' => 'error'), $this->helper->getAttributes());
    }

    public function testRendersNestedMessageSetsAsAFlatList()
    {
        $messages = array(
            array(
                'First validator message',
            ),
            array(
                'Second validator first message',
                'Second validator second message',
            ),
        );
        $element  = new Element('foo');
        $element->setMessages($messages);

        $markup = $this->helper->render($element, array('class' => 'error'));
        $this->assertRegexp('#<ul class="error">\s*<li>First validator message</li>\s*<li>Second validator first message</li>\s*<li>Second validator second message</li>\s*</ul>#s', $markup);
    }

    public function testCallingTheHelperToRenderInvokeCanReturnObject()
    {
        $helper = $this->helper;
        $this->assertEquals($helper(), $helper);
    }

    public function testCanTranslateContent()
    {
        $messages = array(array(
            'First validator message',
        ));

        $element = new Element('foo');
        $element->setMessages($messages);

        $mockTranslator = $this->getMock('Zend\I18n\Translator\Translator');
        $mockTranslator->expects($this->exactly(1))
                       ->method('translate')
                       ->will($this->returnValue('translated content'));

        $this->helper->setTranslator($mockTranslator);
        $this->assertTrue($this->helper->hasTranslator());

        $markup = $this->helper->__invoke($element);
        $this->assertRegexp('#<ul[^>]*>\s*<li>translated content</li>\s*</ul>#s', $markup);
    }

    public function testTranslatorMethods()
    {
        $translatorMock = $this->getMock('Zend\I18n\Translator\Translator');
        $this->helper->setTranslator($translatorMock, 'foo');

        $this->assertEquals($translatorMock, $this->helper->getTranslator());
        $this->assertEquals('foo', $this->helper->getTranslatorTextDomain());
        $this->assertTrue($this->helper->hasTranslator());
        $this->assertTrue($this->helper->isTranslatorEnabled());

        $this->helper->setTranslatorEnabled(false);
        $this->assertFalse($this->helper->isTranslatorEnabled());
    }


}

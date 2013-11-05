<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Form\Element;

use stdClass;
use ArrayObject;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Form\Element;
use Zend\Form\Element\Collection as Collection;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\Stdlib\Hydrator\ObjectProperty as ObjectPropertyHydrator;
use ZendTest\Form\TestAsset\Entity\Product;
use Zend\Stdlib\Hydrator\ArraySerializable;
use ZendTest\Form\TestAsset\CustomCollection;
use ZendTest\Form\TestAsset\ArrayModel;

class CollectionTest extends TestCase
{
    protected $form;
    protected $productFieldset;

    public function setUp()
    {
        $this->form = new \ZendTest\Form\TestAsset\FormCollection();
        $this->productFieldset = new \ZendTest\Form\TestAsset\ProductFieldset();

        parent::setUp();
    }

    public function testCanRetrieveDefaultPlaceholder()
    {
        $placeholder = $this->form->get('colors')->getTemplatePlaceholder();
        $this->assertEquals('__index__', $placeholder);
    }

    public function testCannotAllowNewElementsIfAllowAddIsFalse()
    {
        $collection = $this->form->get('colors');

        $this->assertTrue($collection->allowAdd());
        $collection->setAllowAdd(false);
        $this->assertFalse($collection->allowAdd());

        // By default, $collection contains 2 elements
        $data = array();
        $data[] = 'blue';
        $data[] = 'green';

        $collection->populateValues($data);
        $this->assertEquals(2, count($collection->getElements()));

        $this->setExpectedException('Zend\Form\Exception\DomainException');
        $data[] = 'orange';
        $collection->populateValues($data);
    }

    public function testCanAddNewElementsIfAllowAddIsTrue()
    {
        $collection = $this->form->get('colors');
        $collection->setAllowAdd(true);
        $this->assertTrue($collection->allowAdd());

        // By default, $collection contains 2 elements
        $data = array();
        $data[] = 'blue';
        $data[] = 'green';

        $collection->populateValues($data);
        $this->assertEquals(2, count($collection->getElements()));

        $data[] = 'orange';
        $collection->populateValues($data);
        $this->assertEquals(3, count($collection->getElements()));
    }

    public function testCanValidateFormWithCollectionWithoutTemplate()
    {
        $this->form->setData(array(
            'colors' => array(
                '#ffffff',
                '#ffffff'
            ),
            'fieldsets' => array(
                array(
                    'field' => 'oneValue',
                    'nested_fieldset' => array(
                        'anotherField' => 'anotherValue'
                    )
                ),
                array(
                    'field' => 'twoValue',
                    'nested_fieldset' => array(
                        'anotherField' => 'anotherValue'
                    )
                )
            )
        ));

        $this->assertEquals(true, $this->form->isValid());
    }

    public function testCanValidateFormWithCollectionWithTemplate()
    {
        $collection = $this->form->get('colors');

        $this->assertFalse($collection->shouldCreateTemplate());
        $collection->setShouldCreateTemplate(true);
        $this->assertTrue($collection->shouldCreateTemplate());

        $collection->setTemplatePlaceholder('__template__');

        $this->form->setData(array(
            'colors' => array(
                '#ffffff',
                '#ffffff'
            ),
            'fieldsets' => array(
                array(
                    'field' => 'oneValue',
                    'nested_fieldset' => array(
                        'anotherField' => 'anotherValue'
                    )
                ),
                array(
                    'field' => 'twoValue',
                    'nested_fieldset' => array(
                        'anotherField' => 'anotherValue'
                    )
                )
            )
        ));

        $this->assertEquals(true, $this->form->isValid());
    }

    public function testThrowExceptionIfThereAreLessElementsAndAllowRemoveNotAllowed()
    {
        $this->setExpectedException('Zend\Form\Exception\DomainException');

        $collection = $this->form->get('colors');
        $collection->setAllowRemove(false);

        $this->form->setData(array(
            'colors' => array(
                '#ffffff'
            ),
            'fieldsets' => array(
                array(
                    'field' => 'oneValue',
                    'nested_fieldset' => array(
                        'anotherField' => 'anotherValue'
                    )
                ),
                array(
                    'field' => 'twoValue',
                    'nested_fieldset' => array(
                        'anotherField' => 'anotherValue'
                    )
                )
            )
        ));

        $this->form->isValid();
    }

    public function testCanValidateLessThanSpecifiedCount()
    {
        $collection = $this->form->get('colors');
        $collection->setAllowRemove(true);

        $this->form->setData(array(
            'colors' => array(
                '#ffffff'
            ),
            'fieldsets' => array(
                array(
                    'field' => 'oneValue',
                    'nested_fieldset' => array(
                        'anotherField' => 'anotherValue'
                    )
                ),
                array(
                    'field' => 'twoValue',
                    'nested_fieldset' => array(
                        'anotherField' => 'anotherValue'
                    )
                )
            )
        ));

        $this->assertEquals(true, $this->form->isValid());
    }

    public function testSetOptions()
    {
        $collection = $this->form->get('colors');
        $element = new Element('foo');
        $collection->setOptions(array(
                                  'target_element' => $element,
                                  'count' => 2,
                                  'allow_add' => true,
                                  'allow_remove' => false,
                                  'should_create_template' => true,
                                  'template_placeholder' => 'foo',
                             ));
        $this->assertInstanceOf('Zend\Form\Element', $collection->getOption('target_element'));
        $this->assertEquals(2, $collection->getOption('count'));
        $this->assertEquals(true, $collection->getOption('allow_add'));
        $this->assertEquals(false, $collection->getOption('allow_remove'));
        $this->assertEquals(true, $collection->getOption('should_create_template'));
        $this->assertEquals('foo', $collection->getOption('template_placeholder'));
    }

    public function testSetObjectNullRaisesException()
    {
        $collection = $this->form->get('colors');
        $this->setExpectedException('Zend\Form\Exception\InvalidArgumentException');
        $collection->setObject(null);
    }

    public function testPopulateValuesNullRaisesException()
    {
        $collection = $this->form->get('colors');
        $this->setExpectedException('Zend\Form\Exception\InvalidArgumentException');
        $collection->populateValues(null);
    }

    public function testSetTargetElementNullRaisesException()
    {
        $collection = $this->form->get('colors');
        $this->setExpectedException('Zend\Form\Exception\InvalidArgumentException');
        $collection->setTargetElement(null);
    }

    public function testGetTargetElement()
    {
        $collection = $this->form->get('colors');
        $element = new Element('foo');
        $collection->setTargetElement($element);

        $this->assertInstanceOf('Zend\Form\Element', $collection->getTargetElement());
    }

    public function testExtractFromObjectDoesntTouchOriginalObject()
    {
        $form = new \Zend\Form\Form();
        $form->setHydrator(new \Zend\Stdlib\Hydrator\ClassMethods());
        $this->productFieldset->setUseAsBaseFieldset(true);
        $form->add($this->productFieldset);

        $originalObjectHash = spl_object_hash($this->productFieldset->get("categories")->getTargetElement()->getObject());

        $product = new Product();
        $product->setName("foo");
        $product->setPrice(42);
        $cat1 = new \ZendTest\Form\TestAsset\Entity\Category();
        $cat1->setName("bar");
        $cat2 = new \ZendTest\Form\TestAsset\Entity\Category();
        $cat2->setName("bar2");

        $product->setCategories(array($cat1,$cat2));

        $form->bind($product);

        $form->setData(
            array("product"=>
                array(
                    "name" => "franz",
                    "price" => 13,
                    "categories" => array(
                        array("name" => "sepp"),
                        array("name" => "herbert")
                    )
                )
            )
        );

        $objectAfterExtractHash = spl_object_hash($this->productFieldset->get("categories")->getTargetElement()->getObject());

        $this->assertSame($originalObjectHash,$objectAfterExtractHash);
    }

    public function testDoesNotCreateNewObjects()
    {
        if (!extension_loaded('intl')) {
            // Required by \Zend\I18n\Validator\Float
            $this->markTestSkipped('ext/intl not enabled');
        }

        $form = new \Zend\Form\Form();
        $form->setHydrator(new \Zend\Stdlib\Hydrator\ClassMethods());
        $this->productFieldset->setUseAsBaseFieldset(true);
        $form->add($this->productFieldset);

        $product = new Product();
        $product->setName("foo");
        $product->setPrice(42);
        $cat1 = new \ZendTest\Form\TestAsset\Entity\Category();
        $cat1->setName("bar");
        $cat2 = new \ZendTest\Form\TestAsset\Entity\Category();
        $cat2->setName("bar2");

        $product->setCategories(array($cat1,$cat2));

        $form->bind($product);

        $form->setData(
            array("product"=>
                array(
                    "name" => "franz",
                    "price" => 13,
                    "categories" => array(
                        array("name" => "sepp"),
                        array("name" => "herbert")
                    )
                )
            )
        );
        $form->isValid();

        $categories = $product->getCategories();
        $this->assertSame($categories[0], $cat1);
        $this->assertSame($categories[1], $cat2);
    }

    public function testCreatesNewObjectsIfSpecified()
    {
        if (!extension_loaded('intl')) {
            // Required by \Zend\I18n\Validator\Float
            $this->markTestSkipped('ext/intl not enabled');
        }

        $this->productFieldset->setUseAsBaseFieldset(true);
        $categories = $this->productFieldset->get('categories');
        $categories->setOptions(array(
            'create_new_objects' => true,
        ));

        $form = new \Zend\Form\Form();
        $form->setHydrator(new \Zend\Stdlib\Hydrator\ClassMethods());
        $form->add($this->productFieldset);

        $product = new Product();
        $product->setName("foo");
        $product->setPrice(42);
        $cat1 = new \ZendTest\Form\TestAsset\Entity\Category();
        $cat1->setName("bar");
        $cat2 = new \ZendTest\Form\TestAsset\Entity\Category();
        $cat2->setName("bar2");

        $product->setCategories(array($cat1,$cat2));

        $form->bind($product);

        $form->setData(
            array("product"=>
                array(
                    "name" => "franz",
                    "price" => 13,
                    "categories" => array(
                        array("name" => "sepp"),
                        array("name" => "herbert")
                    )
                )
            )
        );
        $form->isValid();

        $categories = $product->getCategories();
        $this->assertNotSame($categories[0], $cat1);
        $this->assertNotSame($categories[1], $cat2);
    }

    public function testExtractDefaultIsEmptyArray()
    {
        $collection = $this->form->get('fieldsets');
        $this->assertEquals(array(), $collection->extract());
    }

    public function testExtractThroughTargetElementHydrator()
    {
        $collection = $this->form->get('fieldsets');
        $this->prepareForExtract($collection);

        $expected = array(
            'obj2' => array('field' => 'fieldOne'),
            'obj3' => array('field' => 'fieldTwo'),
        );

        $this->assertEquals($expected, $collection->extract());
    }

    public function testExtractMaintainsTargetElementObject()
    {
        $collection = $this->form->get('fieldsets');
        $this->prepareForExtract($collection);

        $expected = $collection->getTargetElement()->getObject();

        $collection->extract();

        $test = $collection->getTargetElement()->getObject();

        $this->assertSame($expected, $test);
    }

    public function testExtractThroughCustomHydrator()
    {
        $collection = $this->form->get('fieldsets');
        $this->prepareForExtract($collection);

        $mockHydrator = $this->getMock('Zend\Stdlib\Hydrator\HydratorInterface');
        $mockHydrator->expects($this->exactly(2))
                     ->method('extract')
                     ->will($this->returnCallback(function ($object) {
                         return $object->field . '_foo';
                     }));

        $collection->setHydrator($mockHydrator);

        $expected = array(
            'obj2' => 'fieldOne_foo',
            'obj3' => 'fieldTwo_foo',
        );

        $this->assertEquals($expected, $collection->extract());
    }

    public function testExtractFromTraversable()
    {
        $collection = $this->form->get('fieldsets');
        $this->prepareForExtract($collection);

        $traversable = new ArrayObject($collection->getObject());
        $collection->setObject($traversable);

        $expected = array(
            'obj2' => array('field' => 'fieldOne'),
            'obj3' => array('field' => 'fieldTwo'),
        );

        $this->assertEquals($expected, $collection->extract());
    }

    public function testValidateData()
    {
        $myFieldset = new Fieldset();
        $myFieldset->add(array(
            'name' => 'email',
            'type' => 'Email',
        ));

        $myForm = new Form();
        $myForm->add(array(
            'name' => 'collection',
            'type' => 'Collection',
            'options' => array(
                'target_element' => $myFieldset,
            ),
        ));

        $data = array(
            'collection' => array(
                array('email' => 'test1@test1.com'),
                array('email' => 'test2@test2.com'),
                array('email' => 'test3@test3.com'),
            )
        );

        $myForm->setData($data);

        $this->assertTrue($myForm->isValid());
        $this->assertEmpty($myForm->getMessages());
    }

    protected function prepareForExtract($collection)
    {
        $targetElement = $collection->getTargetElement();

        $obj1 = new stdClass();

        $targetElement->setHydrator(new ObjectPropertyHydrator())
                      ->setObject($obj1);

        $obj2 = new stdClass();
        $obj2->field = 'fieldOne';

        $obj3 = new stdClass();
        $obj3->field = 'fieldTwo';

        $collection->setObject(array(
            'obj2' => $obj2,
            'obj3' => $obj3,
        ));
    }

    public function testCanBindObjectAndPopulateAndExtractNestedFieldsets()
    {

        $productFieldset = new \ZendTest\Form\TestAsset\ProductFieldset();
        $productFieldset->setHydrator(new \Zend\Stdlib\Hydrator\ClassMethods());

        $mainFieldset = new Fieldset('shop');
        $mainFieldset->setHydrator(new \Zend\Stdlib\Hydrator\ClassMethods());
        $mainFieldset->add($productFieldset);

        $form = new Form();
        $form->setHydrator(new ObjectPropertyHydrator());
        $form->add(array(
            'name' => 'collection',
            'type' => 'Collection',
            'options' => array(
                'target_element' => $mainFieldset,
                'count' => 2
            ),
        ));
        $form->get('collection')->setHydrator(new ObjectPropertyHydrator());

        $market = new stdClass();

        $prices = array(100, 200);

        $shop1 = new stdClass();
        $shop1->product = new Product();
        $shop1->product->setPrice($prices[0]);

        $shop2 = new stdClass();
        $shop2->product = new Product();
        $shop2->product->setPrice($prices[1]);

        $market->collection = array($shop1, $shop2);
        $form->bind($market);

        //test for object binding
        foreach ($form->get('collection')->getFieldsets() as $_fieldset) {
            $this->assertInstanceOf('ZendTest\Form\TestAsset\Entity\Product', $_fieldset->get('product')->getObject());
        };

        //test for correct extract and populate
        foreach ($prices as $_k => $_price) {
            $this->assertEquals($_price, $form->get('collection')->get($_k)->get('product')->get('price')->getValue());
        }
    }

    public function testExtractFromTraversableImplementingToArrayThroughCollectionHydrator()
    {
        $collection = $this->form->get('fieldsets');

        // this test is using a hydrator set on the collection
        $collection->setHydrator(new ArraySerializable());

        $this->prepareForExtractWithCustomTraversable($collection);

        $expected = array(
            array('foo' => 'foo_value_1', 'bar' => 'bar_value_1', 'foobar' => 'foobar_value_1'),
            array('foo' => 'foo_value_2', 'bar' => 'bar_value_2', 'foobar' => 'foobar_value_2'),
        );

        $this->assertEquals($expected, $collection->extract());
    }

    public function testExtractFromTraversableImplementingToArrayThroughTargetElementHydrator()
    {
        $collection = $this->form->get('fieldsets');

        // this test is using a hydrator set on the target element of the collection
        $targetElement = $collection->getTargetElement();
        $targetElement->setHydrator(new ArraySerializable());
        $obj1 = new ArrayModel();
        $targetElement->setObject($obj1);

        $this->prepareForExtractWithCustomTraversable($collection);

        $expected = array(
            array('foo' => 'foo_value_1', 'bar' => 'bar_value_1', 'foobar' => 'foobar_value_1'),
            array('foo' => 'foo_value_2', 'bar' => 'bar_value_2', 'foobar' => 'foobar_value_2'),
        );

        $this->assertEquals($expected, $collection->extract());
    }

    protected function prepareForExtractWithCustomTraversable($collection)
    {
        $obj2 = new ArrayModel();
        $obj2->exchangeArray(array('foo' => 'foo_value_1', 'bar' => 'bar_value_1', 'foobar' => 'foobar_value_1'));
        $obj3 = new ArrayModel();
        $obj3->exchangeArray(array('foo' => 'foo_value_2', 'bar' => 'bar_value_2', 'foobar' => 'foobar_value_2'));

        $traversable = new CustomCollection();
        $traversable->append($obj2);
        $traversable->append($obj3);
        $collection->setObject($traversable);
    }
}

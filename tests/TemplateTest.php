<?php

/**
 * Part of the Stencil templating framework.
 *
 * @package  Stencil
 * @author   Liam Wye <me@liamwye.me>
 * @license  http://opensource.org/licenses/MIT The MIT license (MIT)
 * @version  0.3
 */
namespace Stencil;

require_once '../src/Exceptions/StencilNotFoundException.php';
require_once '../src/Observer/ObservableInterface.php';
require_once '../src/Observer/Observable.php';
require_once '../src/TemplateInterface.php';
require_once '../src/Template.php';

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that the constructor sets the correct identifier.
     */
    public function testConstructSetIdentifier()
    {
        $template = new Template('testIdentifier');

        $this->assertEquals('testIdentifier', $template->getIdentifier());
    }

    /**
     * Test that the constructor sets information passed in the config array.
     */
    public function testConstructSetConfig()
    {
        $template = new Template('testIdentifier', array(
            'path'    => 'TemplateTest.php', // This exists and is a valid path
            'inherit' => true,
        ));

        $this->assertTrue($template->getInherit());
    }

    /**
     * Test that the contructor throws an exception when no path is given in
     * the config array.
     *
     * @expectedException           \Stencil\Exceptions\StencilNotFoundException
     * @expectedExceptionMessage    A Stencil path is required to initiate a template.
     */
    public function testConstructSetConfigWithNoPath()
    {
        $template = new Template('testIdentifier', array(
            'inherit' => true,
        ));
    }

    /**
     * Test that the constructor throws an exception when an invlid path is given
     * in the config array.
     *
     * @expectedException           \Stencil\Exceptions\StencilNotFoundException
     * @expectedExceptionMessage    Unable to find Stencil file.
     */
    public function testConstructSetConfigWithInvalidPath()
    {
        $template = new Template('testIdentifier', array(
            'path' => 'this path does not exist',
        ));
    }

    /**
     * Test that configuration options set within the constructor are case insensitive.
     */
    public function testConstructSetConfigIsCaseInsensitive()
    {
        $template = new Template('testIdentifier', array(
            'path'        => 'TemplateTest.php',
            'TestElEmeNT' => '123',
        ));

        $this->assertEquals('123', $this->getTestElement());
    }

    /**
     * Test that the dynamic getter functionality returns the correct data.
     */
    public function testDynamicGetMethod()
    {
        $template = new Template('testIdentifier', array(
            'path'        => 'TemplateTest.php',
            'testElement' => '123',
        ));

        $this->assertEquals('123', $template->getTestElement());
    }

    /**
     * Test that the dynamic setter functionality sets the correct data.
     */
    public function testDynamicSetMethod()
    {
        $template = new Template('testIdentifier');
        $template->setTestElement('123');

        $this->assertEquals('123', $template->getTestElement());
    }

    /**
     * Test that the dynamic setter functionality sets the identifier correctly.
     * This is stored seperately to the other configuration options.
     */
    public function testDynamicSetIdentifierMethod()
    {
        $template = new Template('testIdentifier');
        $template->setIdentifier('updatedIdentifier');

        $this->assertEquals('updatedIdentifier', $template->getIdentifier());
    }

    /**
     * Test that the dynamic setter functionality sets and validates the path
     * correctly. This is handled the same way as other setters, but the path
     * is checked.
     */
    public function testDynamicSetPathMethod()
    {
        $template = new Template('testIdentifier');
        $template->setPath('TemplateTest.php');

        $this->assertEquals('TemplateTest.php', $template->getPath());
    }

    /**
     * Test that the dynamic setter functionality correctly throws an exception
     * for incorrect paths. This setter is handled the same way as other setters,
     * but the path is checked.
     *
     * @expectedException           \Stencil\Exceptions\StencilNotFoundException
     * @expectedExceptionMessage    Unable to find Stencil file.
     */
    public function testDynamicSetInvalidPathMethod()
    {
        $template = new Template('testIdentifier');
        $template->setPath('this path does not exist');
    }

    /**
     * Test that the dynamic getters and setters are case insensitive.
     */
    public function testDynamicGetAndSetIsCaseInsensitive()
    {
        $template = new Template('testIdentifier', array(
            'path' => 'TemplateTest.php',
        ));

        $template->setThisisnotCaseSensiTive('123');

        $this->assertEquals('123', $template->getThisIsNotCaseSensitive());
    }

    /**
     * Test that the dynamic getter/setter does not allow other prefixes to select
     * data.
     *
     * @expectedException           \BadMethodCallException
     * @expectedExceptionMessage    Call to undefined method.
     */
    public function testDynamicMethodOtherThanGetOrSet()
    {
        $template = new Template('testIdentifier', array(
            'path'     => 'TemplateTest.php',
            'testTest' => '123',
            'test'     => '345',
        ));
        $template->testTest();
    }
}

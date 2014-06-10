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
            'path'    => 'test.stencil.php', // This exists and is a valid path
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
            'path'        => 'test.stencil.php',
            'TestElEmeNT' => '123',
        ));

        $this->assertEquals('123', $template->getTestElement());
    }

    /**
     * Test that the dynamic getter functionality returns the correct data.
     */
    public function testDynamicGetMethod()
    {
        $template = new Template('testIdentifier', array(
            'path'        => 'test.stencil.php',
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
        $template->setPath('test.stencil.php');

        $this->assertEquals('test.stencil.php', $template->getPath());
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
            'path' => 'test.stencil.php',
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
    public function testDynamicMethodUsingADifferentPrefix()
    {
        $template = new Template('testIdentifier', array(
            'path'     => 'test.stencil.php',
            'testTest' => '123',
            'test'     => '345',
        ));
        $template->testTest();
    }

    /**
     * Test that template variables are set and rendered appropriately.
     */
    public function testSetTemplateVariable()
    {
        $template = new Template('testIdentifier', array(
            'path' => 'test.stencil.php',
        ));
        $template->set('testvar', '123');
        $template->set('testvar2', '456');

        $result = $template->render();

        $this->assertEquals('<h1>123</h1><br /><p>456</p>', $result);
    }

    /**
     * Test that an array of template variables can be set and rendered properly.
     */
    public function testSetArrayOfTemplateVariables()
    {
        $template = new Template('testIdentifier', array(
            'path' => 'test.stencil.php',
        ));
        $template->setArray(array(
            'testvar'  => '123',
            'testvar2' => '456',
        ));

        $result = $template->render();

        $this->assertEquals('<h1>123</h1><br /><p>456</p>', $result);
    }

    /**
     * Test that an array of template variables can be added to existing template
     * variables without removing any existing template variables.
     */
    public function testSetArrayOfTemplateVariablesAddingToExistingVariables()
    {
        $template = new Template('testIdentifier', array(
            'path' => 'test.stencil.php',
        ));
        $template->set('testvar', '123');

        $template->setArray(array(
            'testvar2' => '111',
        ));

        $result = $template->render();

        $this->assertEquals('<h1>123</h1><br /><p>111</p>', $result);
    }

    /**
     * Test that an array of template variables can be added to the template
     * overwriting any existing template variables.
     * @return [type] [description]
     */
    public function testSetArrayOfTemplateVariablesAndOverwriteExistingVariables()
    {
        $template = new Template('testIdentifier', array(
            'path' => 'test.stencil.php',
        ));
        $template->set('testvar', '123');
        $template->set('testvar2', '456');

        $template->setArray(array(
            'testvar'  => '999',
            'testvar2' => '111',
        ), true);

        $result = $template->render();

        $this->assertEquals('<h1>999</h1><br /><p>111</p>', $result);
    }

    /**
     * Test that rendering the template without setting all the variables
     * renders properly but ommits the variable.
     */
    public function testRenderTemplateWithoutSettingVariables()
    {
        $template = new Template('testIdentifier', array(
            'path' => 'test.stencil.php',
        ));

        $result = $template->render();

        $this->assertEquals('<h1></h1><br /><p></p>', $result);
    }

    // Extend
    // Extend with identifier checks
    // Extend with config checks
    // Render with inherit
    // Template events
}

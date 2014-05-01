<?php

/**
 * Part of the Stencil templating framework. A basic PHP templating library
 * for handling your templating requirements.
 *
 * @package Wye\Stencil
 * @author  Liam Wye <me@liamwye.me>
 * @license  http://opensource.org/licenses/MIT The MIT license (MIT)
 * @version  0.2.1
 */
namespace Wye\Stencil;

/**
 * Simple Templating interface
 */
interface ITemplate
{
    public function set($name, $value);
    public function setArray($array, $overwrite);
    public function render();
}

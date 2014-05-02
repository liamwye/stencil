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

/**
 * Templating interface
 */
interface TemplateInterface
{
    public function set($name, $value);

    public function setArray($array, $overwrite);

    public function render();
}

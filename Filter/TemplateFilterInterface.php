<?php

/**
 * Part of the Stencil templating framework.
 *
 * @package  Stencil\Filter
 * @author   Liam Wye <me@liamwye.me>
 * @license  http://opensource.org/licenses/MIT The MIT license (MIT)
 * @version  0.3
 */
namespace Stencil\Filter;

/**
 * Interface for Stencil Filters.
 *
 * Roughly based on an Intercepting Filter model with pre and post processing of templates.
 */
interface TemplateFilterInterface
{
    /**
     * Processing to be run before a template is rendered.
     *
     * @return void
     */
    public function preProcess();

    /**
     * Processing to be run after the template has been rendered.
     *
     * @param string $buffer The template buffer.
     *
     * @return string
     */
    public function postProcess($buffer);
}

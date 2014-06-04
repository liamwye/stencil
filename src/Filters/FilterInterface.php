<?php

/**
 * Part of the Stencil templating framework.
 *
 * @package  Stencil\Filter
 * @author   Liam Wye <me@liamwye.me>
 * @license  http://opensource.org/licenses/MIT The MIT license (MIT)
 * @version  0.3
 */
namespace Stencil\Filters;

/**
 * Interface for Stencil filters.
 */
interface FilterInterface
{
    /**
     * Used to process data passed from a Stencil template.
     *
     * @param  Array $context  The data to process.
     * @return Array           The processed context array.
     */
    public function process($context);
}

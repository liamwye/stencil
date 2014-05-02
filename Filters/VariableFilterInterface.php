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
 * Interface for Stencil Variable Filters.
 *
 * Allows filtering to be applied to variables that are set within the template.
 */
interface VariableFilterInterface
{
    /**
     * Process method to be run on the supplied variable.
     *
     * @param string|array $variables The variables to filter.
     *
     * @return string The processed/filtered variable.
     */
    public function process($variables);
}

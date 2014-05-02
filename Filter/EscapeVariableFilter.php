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
 * Apply basic escaping to variables.
 */
class EscapeVariableFilter extends AbstractVariableFilter
{
    /**
     * Apply some basic variable escaping.
     *
     * @see                    htmlspecialchars()
     * @param  mixed $variable The variable to process.
     * @return mixed           The variable once processed.
     */
    protected function each($variable) {
        if (is_string($variable)) {
            $variable = htmlspecialchars($variable, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        return $variable;
    }
}

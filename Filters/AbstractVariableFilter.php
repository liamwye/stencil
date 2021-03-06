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
 *
 */
abstract class AbstractVariableFilter implements FilterInterface
{
    /**
     * Provide an abstract implementation of filter which provides a basis for
     * a basic variable filter. Each variable is iterated over and a function is
     * called seperately on each.
     */
    public function process($context)
    {
        // Process the array of variables recursively
        array_walk_recursive($context['variables'], array($this, 'each'));

        return $context;
    }

    /**
     * Will be called once for each variable passed in the context array.
     *
     * @param  Mixed  $variable The variable.
     * @param  String $key      The variable context key.
     */
    abstract protected function each(&$variable, $key);
}

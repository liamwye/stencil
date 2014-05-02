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
 * Provides a basic implementation of VariableFilterInterface that introduces
 * utility methods to allow easier extensibility.
 */
abstract class AbstractVariableFilter implements VariableFilterInterface
{
    /**
     * A basic implementation of the process() method.
     * This will iterate through all of the template variables and fire a method
     * on each variable for processing.
     * This will allow a child classes to provide a very small and efficient filter.
     *
     * @param string|array $variables The variables to filter.
     * @return string The processed/filtered variable.
     */
    public function process($variables) {
        if (is_array($variables)) {
            foreach ($variables as $key => $value) {
                if (is_array($value)) {
                    // Run the process recursively within the array to see if there are any strings to escape...
                    $value = $this->process($value);
                } else {
                    $variables[$key] = $this->each($value);
                }
            }
        }

        return $variables;
    }

    /**
     * Abstract method that will be called by process() once for each variable.
     *
     * @param  mixed $variable The variable from process().
     * @return mixed           The variable once any processing has been carried
     *                         out.
     */
    abstract protected function each($variable);
}

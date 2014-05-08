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
 * Provides a basic implementation of FilterInterface that introduces
 * utility methods to allow easier extensibility.
 *
 * Todo: Refine - wont work as context is passed as opposed to $variables
 */
abstract class AbstractFilter implements FilterInterface
{
    /**
     * The filter context.
     * @var array
     */
    protected $context = array();

    /**
     * A basic implementation of the process bootstrapper method.
     * Our abstract implementation uses process to initiate the context internally
     * and provide helper methods to access the context.
     *
     * @param  Array  $context The context array.
     * @return String          The result of the filter processing.
     */
    public function process(Array $context) {
        $this->context = $context;

        // Run the core filter method; this is where the filter functionality would go
        $result = $this->filter();

        return $result;
    }

    /**
     * Extract the buffer from the filter context.
     *
     * @return String The template buffer.
     */
    public function getBuffer() {

        return $this->getContext('buffer');
    }

    /**
     * A collection of template variables.
     *
     * @return Array The template variables.
     */
    public function getVariables() {
        return $this->getContext('variables');
    }

    /**
     * Extract a specified key from the context array.
     * @param  String $key The key to return. E.g. 'buffer' or 'variables'.
     * @return Mixed       The context value or null.
     */
    public function getContext($key) {
        // Provide some uniformity
        $key = strtolower($key);

        if (array_key_exists($key, $this->context)) {
            return $this->context[$key];
        }

        return null;
    }

    /**
     * The filter processing.
     *
     * @return String The processed context.
     */
    abstract protected function filter();
}

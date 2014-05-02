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
class EscapeVariableFilter implements VariableFilterInterface
{
    /**
     * {@inheritdocs}
     */
    public function process($variables)
    {
        if (is_array($variables)) {
            foreach ($variables as $key => $value) {
                if (is_array($value)) {
                    # Run the process recursively within the array to see if there are any strings to escape...
                    $value = $this->process($value);
                } elseif (is_string($value)) {
                    # Do some basic escaping...
                    $variables[$key] = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                }
            }
        }

        return $variables;
    }
}

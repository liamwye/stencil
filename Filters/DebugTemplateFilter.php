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
 * Wrap template output with debugging comments.
 */
class DebugTemplateFilter
{
    public function filter() {
        $buffer = $this->getBuffer();

        // Show a different set of comments for empty templates
        if (empty($template)) {
            $template = PHP_EOL . '<!-- [Stencil]: Empty Stencil \'' . $this->name . '\' -->' . PHP_EOL;
        } else {
            $template = PHP_EOL . '<!-- [Stencil]: Start \'' . $this->name . '\' -->' . PHP_EOL
                . $template
                . PHP_EOL . '<!-- [Stencil]: End \'' . $this->name . '\' -->' . PHP_EOL;
        }

        return $buffer;
    }
}

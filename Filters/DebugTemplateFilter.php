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
class DebugTemplateFilter implements FilterInterface
{
    public function process($context) {
        // Show a different set of comments for empty templates
        if (empty($context['buffer'])) {
            $context['buffer'] = '<!-- [Stencil]: Empty Stencil \'' . $context['identifier'] . '\' -->';
        } else {
            $context['buffer'] = '<!-- [Stencil]: Start \'' . $context['identifier'] . '\' -->' . PHP_EOL
                . $context['buffer']
                . PHP_EOL . '<!-- [Stencil]: End \'' . $context['identifier'] . '\' -->' . PHP_EOL;
        }

        return $context;
    }
}

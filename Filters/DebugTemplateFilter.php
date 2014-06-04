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
        $comment = '<!-- /stencil/' . strtolower($context['identifier']) . ' -->';

        // Wrap the buffer with the debug comments
        if (empty($context['buffer'])) {
            $context['buffer'] = $comment;
        } else {
            $context['buffer'] = $comment . PHP_EOL .
                $context['buffer'] . PHP_EOL .
                $comment . PHP_EOL;
        }

        return $context;
    }
}

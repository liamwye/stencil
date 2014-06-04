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
 * Implementation of a TemplateFilter to minify template output.
 */
class MinifyTemplateFilter implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($context)
    {
        $regex = '%' .                                  # Collapse ws everywhere but in blacklisted elements.
                    '(?>' .                             # Match all whitespans other than single space.
                      '[^\S ]\s*' .                     # Either one [\t\r\n\f\v] and zero or more ws,
                    '| \s{2,}' .                        # or two or more consecutive-any-whitespace.
                    ')' .                               # Note: The remaining regex consumes no text at all...
                    '(?=' .                             # Ensure we are not in a blacklist tag.
                      '(?:' .                           # Begin (unnecessary) group.
                        '(?:' .                         # Zero or more of...
                          '[^<]++' .                    # Either one or more non-"<"
                        '| <' .                         # or a < starting a non-blacklist tag.
                          '(?!/?(?:textarea|pre)\b)' .
                        ')*+' .                         # (This could be "unroll-the-loop"ified.)
                      ')' .                             # End (unnecessary) group.
                      '(?:' .                           # Begin alternation group.
                        '<' .                           # Either a blacklist start tag.
                        '(?>textarea|pre)\b' .
                      '| \z' .                          # or end of file.
                      ')' .                             # End alternation group.
                    ')' .                               # If we made it here, we are not in a blacklist tag.
                    '%ix';

        // Ensure that the buffer isn't empty
        if (!empty($context['buffer'])) {
            $context['buffer'] = preg_replace($regex, ' ', $context['buffer']);
        }

        return $context;
    }
}

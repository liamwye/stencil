<?php

/**
 * Part of the Stencil templating framework. A basic PHP templating library
 * for handling your templating requirements.
 *
 * @package Wye\Stencil\Filter
 * @author  Liam Wye <me@liamwye.me>
 * @license  http://opensource.org/licenses/MIT The MIT license (MIT)
 * @version  0.2.1
 */
namespace Wye\Stencil\Filter;

/**
 * Implementation of a TemplateFilter to minify template output.
 */
class MinifyTemplateFilter implements ITemplateFilter
{
    /**
     * {@inheritdoc}
     */
    public function preProcess()
    {
        return; # Nothing to do here, move along.
    }

    /**
     * {@inheritdoc}
     */
    public function postProcess($buffer)
    {
        $regex = '%' . 									# Collapse ws everywhere but in blacklisted elements.
                    '(?>' .              				# Match all whitespans other than single space.
                      '[^\S ]\s*' .      				# Either one [\t\r\n\f\v] and zero or more ws,
                    '| \s{2,}' .         				# or two or more consecutive-any-whitespace.
                    ')' .  								# Note: The remaining regex consumes no text at all...
                    '(?=' .             				# Ensure we are not in a blacklist tag.
                      '(?:' .            				# Begin (unnecessary) group.
                        '(?:' .          				# Zero or more of...
                          '[^<]++' .     				# Either one or more non-"<"
                        '| <' .         				# or a < starting a non-blacklist tag.
                          '(?!/?(?:textarea|pre)\b)' .
                        ')*+' .          				# (This could be "unroll-the-loop"ified.)
                      ')' .              				# End (unnecessary) group.
                      '(?:' .            				# Begin alternation group.
                        '<' .            				# Either a blacklist start tag.
                        '(?>textarea|pre)\b' .
                      '| \z' .           				# or end of file.
                      ')' .              				# End alternation group.
                    ')' .   							# If we made it here, we are not in a blacklist tag.
                    '%ix';

        $buffer = preg_replace($regex, ' ', $buffer);

        return $buffer;
    }
}

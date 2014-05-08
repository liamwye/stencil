<?php

/**
 * Part of the Stencil templating framework.
 *
 * @package  Stencil
 * @author   Liam Wye <me@liamwye.me>
 * @license  http://opensource.org/licenses/MIT The MIT license (MIT)
 * @version  0.3
 */
namespace Stencil\Observer;

/**
 * A simple interface for implementing the Observer pattern.
 */
interface ObservableInterface
{
    public function dispatch($event, $parameters);

    public function addListener($listener, $event, $priority);

    public function removeListener($listener, $event);

    public function hasListeners($event);
}

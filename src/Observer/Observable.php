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
 * A simple implementation of the Observer pattern.
 * Will allow events to be fired and received to provide pre and post processing.
 */
class Observable implements ObservableInterface
{
    /**
     * A collection of listeners that need to be notified on particular events.
     * @var array
     */
    protected $listeners = array();

    /**
     * Dispatch an event to any listeners.
     *
     * @param  String $event      The event name to dispatch.
     * @param  Array  $parameters The parameters to pass to any listeners.
     * @return Boolean            Whether any listeners were notified.
     */
    public function dispatch($event, &$parameters = array())
    {
        // Ensure we've got listeners to dispatch to..
        if ($this->hasListeners($event)) {
            foreach ($this->listeners[$event] as $priority => $listeners) {
                // Numeric index so we can manually loop for efficiency
                for ($i = 0, $listenerCount = count($listeners); $i < $listenerCount; $i++) {
                    // Add the event to the parameters we pass
                    array_unshift($parameters, $event);

                    // Attempt to call the event method on the listener
                    $parameters = call_user_func($this->listeners[$event][$priority][$i], $parameters);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Add a listener to the specified event.
     * A priority can be provided to define the order in which listeners are
     * notified.
     *
     * @param Callable  $listener  The listener to be notified.
     * @param String    $event     The name of the event to listen to.
     * @param Integer   $priority  The priority to add the listener with (0 based)
     */
    public function addListener($listener, $event, $priority = '5')
    {
        // Build the listener callable
        if (!is_array($listener)) {
            $listener = array($listener, 'process');
        }

        // Check to ensure that our array structure has been built
        if (!array_key_exists($event, $this->listeners)) {
            $this->listeners[$event] = array();
        }
        if (!array_key_exists($priority, $this->listeners[$event])) {
            $this->listeners[$event][$priority] = array();
        }

        // Add the listener
        $this->listeners[$event][$priority][] = $listener;
    }

    /**
     * Remove a listener from the specified event.
     *
     * @param  Callable  $listener Listener to remove.
     * @param  String    $event    Event to remove the listener from.
     * @return Boolean             Whether a listener was removed.
     */
    public function removeListener($listener, $event)
    {
        // Loop through an event listing to try and find the listener given
        if ($this->hasListeners($event)) {
            foreach ($this->listeners[$event] as $priority => $listeners) {
                // Search the array to see if we can find the listener we're after
                $key = array_search($listener, $listeners);

                if (false !== $key) {
                    unset($this->listeners[$event][$priority][$key]);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check whether an event has any listeners attached.
     *
     * @param  String  $event The event to check.
     * @return Boolean        True if listeners are found, false otherwise.
     */
    public function hasListeners($event)
    {
        // Check we're dealing with an existing event
        if (!empty($this->listeners[$event])) {
            return (boolean) count($this->listeners[$event]);
        }

        return false;
    }
}

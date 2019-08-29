<?php
/**
 * Created by PhpStorm.
 * User: debian
 * Date: 19-8-18
 * Time: 上午12:59
 */

/*
 +------------------------------------------------------------------------+
 | Code from Phalcon Framework                                            |
 +------------------------------------------------------------------------+
 | Phalcon Team (https://phalconphp.com)                                  |
 +------------------------------------------------------------------------+
 | Source of Phalcon (https://github.com/phalcon/cphalcon)                |
 +------------------------------------------------------------------------+
 */

namespace Sharedsway\Event;

use SplPriorityQueue;

class Manager implements ManagerInterface
{

    /**
     * @var SplPriorityQueue[]
     */
    protected $_events = null;

    protected $_collect = false;

    protected $_enablePriorities = false;

    protected $_responses;


    /**
     * Attach a listener to the events manager
     * @param null|string $eventType
     * @param $handler
     * @param int|null $priority
     * @throws Exception
     */
    public function attach(?string $eventType, $handler, ?int $priority = 100)
    {

        if (!is_object($handler)) {
            throw new Exception("Event handler must be an Object");
        }

        $priorityQueue = $this->_events[$eventType] ?? null;
        if (!$priorityQueue) {

            if ($this->_enablePriorities) {

                // Create a SplPriorityQueue to store the events with priorities
                $priorityQueue = new SplPriorityQueue();

                // Extract only the Data // Set extraction flags
                $priorityQueue->setExtractFlags(SplPriorityQueue::EXTR_DATA);

                // Append the events to the queue
                $this->_events[$eventType] = $priorityQueue;

            } else {
                $priorityQueue = [];
            }
        }

        // Insert the handler in the queue
        if (is_object($priorityQueue)) {
            $priorityQueue->insert($handler, $priority);
        } else {
            // Append the events to the queue
            $priorityQueue[]           = $handler;
            $this->_events[$eventType] = $priorityQueue;
        }

    }


    /**
     * Detach the listener from the events manager
     * @param null|string $eventType
     * @param $handler
     * @throws Exception
     */
    public function detach(?string $eventType, $handler)
    {

        if (!is_object($handler)) {
            throw new Exception("Event handler must be an Object");
        }
        $priorityQueue = $this->_events[$eventType] ?? null;
        if ($priorityQueue) {

            if (is_object($priorityQueue)) {

                // SplPriorityQueue hasn't method for element deletion, so we need to rebuild queue
                $newPriorityQueue = new SplPriorityQueue();
                $newPriorityQueue->setExtractFlags(SplPriorityQueue::EXTR_DATA);

                $priorityQueue->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
                $priorityQueue->top();

                while ($priorityQueue->valid()) {
                    $data = $priorityQueue->current();
                    $priorityQueue->next();
                    if ($data["data"] !== $handler) {
                        $newPriorityQueue->insert($data["data"], $data["priority"]);
                    }
                }

                $this->_events[$eventType] = $newPriorityQueue;
            } else {
                $key = array_search($handler, $priorityQueue, true);
                if ($key !== false) {
                    unset($priorityQueue[$key]);
                }
                $this->_events[$eventType] = $priorityQueue;
            }
        }
    }


    /**
     * Set if priorities are enabled in the EventsManager
     * @param bool $enablePriorities
     */
    public function enablePriorities(bool $enablePriorities)
    {
        $this->_enablePriorities = $enablePriorities;
    }

    /**
     * Returns if priorities are enabled
     */
    public function arePrioritiesEnabled(): bool
    {
        return $this->_enablePriorities;
    }

    /**
     * Tells the event manager if it needs to collect all the responses returned by every
     * registered listener in a single fire
     * @param bool $collect
     */
    public function collectResponses(bool $collect)
    {
        $this->_collect = $collect;
    }

    /**
     * Check if the events manager is collecting all all the responses returned by every
     * registered listener in a single fire
     */
    public function isCollecting(): bool
    {
        return $this->_collect;
    }

    /**
     * Returns all the responses returned by every handler executed by the last 'fire' executed
     *
     * @return array
     */
    public function getResponses()
    {
        return $this->_responses;
    }


    /**
     * Removes all events from the EventsManager
     * @param null|string $type
     */
    public function detachAll(?string $type = null)
    {
        if ($type === null) {
            $this->_events = null;
        } else {
            if (isset ($this->_events[$type])) {
                unset($this->_events[$type]);
            }
        }
    }


    /**
     * Internal handler to call a queue of events
     *
     * @param $queue
     * @param EventInterface $event
     * @return mixed|null
     * @throws Exception
     */
    public final function fireQueue($queue, EventInterface $event)
    {

        if (!is_array($queue)) {
            if (is_object($queue)) {
                if (!($queue instanceof SplPriorityQueue)) {
                    throw new Exception(
                        sprintf(
                            "Unexpected value type: expected object of type SplPriorityQueue, %s given",
                            get_class($queue)
                        )
                    );
                }
            } else {
                throw new Exception("The queue is not valid");
            }
        }

        $status    = null;
        $arguments = null;

        // Get the event type
        $eventName = $event->getType();
        if (!is_string($eventName)) {
            throw new Exception("The event type not valid");
        }

        // Get the object who triggered the event
        $source = $event->getSource();

        // Get extra data passed to the event
        $data = $event->getData();

        // Tell if the event is cancelable
        $cancelable = (bool)$event->isCancelable();

        // Responses need to be traced?
        $collect = (boolean)$this->_collect;

        if (is_object($queue)) {

            // We need to clone the queue before iterate over it
            $iterator = clone $queue;

            // Move the queue to the top
            $iterator->top();

            while ($iterator->valid()) {

                // Get the current data
                $handler = $iterator->current();
                $iterator->next();

                // Only handler objects are valid
                if (is_object($handler)) {

                    // Check if the event is a closure
                    if ($handler instanceof \Closure) {

                        // Create the closure arguments
                        if ($arguments === null) {
                            $arguments = [$event, $source, $data];
                        }

                        // Call the function in the PHP userland
                        $status = call_user_func_array($handler, $arguments);

                        // Trace the response
                        if ($collect) {
                            $this->_responses[] = $status;
                        }

                        if ($cancelable) {

                            // Check if the event was stopped by the user
                            if ($event->isStopped()) {
                                break;
                            }
                        }

                    } else {

                        // Check if the listener has implemented an event with the same name
                        if (method_exists($handler, $eventName)) {

                            // Call the function in the PHP userland
                            $status = $handler->{$eventName}($event, $source, $data);

                            // Collect the response
                            if ($collect) {
                                $this->_responses[] = $status;
                            }

                            if ($cancelable) {

                                // Check if the event was stopped by the user
                                if ($event->isStopped()) {
                                    break;
                                }
                            }
                        }
                    }
                }
            }

        } else {

            foreach ($queue as $handler)
                //for (handler in queue) {

                // Only handler objects are valid
                if (is_object($handler)) {

                    // Check if the event is a closure
                    if ($handler instanceof \Closure) {

                        // Create the closure arguments
                        if ($arguments === null) {
                            $arguments = [$event, $source, $data];
                        }

                        // Call the function in the PHP userland
                        $status = call_user_func_array($handler, $arguments);

                        // Trace the response
                        if ($collect) {
                            $this->_responses[] = $status;
                        }

                        if ($cancelable) {

                            // Check if the event was stopped by the user
                            if ($event->isStopped()) {
                                break;
                            }
                        }

                    } else {

                        // Check if the listener has implemented an event with the same name
                        if (method_exists($handler, $eventName)) {

                            // Call the function in the PHP userland
                            $status = $handler->{$eventName}($event, $source, $data);

                            // Collect the response
                            if ($collect) {
                                $this->_responses[] = $status;
                            }

                            if ($cancelable) {

                                // Check if the event was stopped by the user
                                if ($event->isStopped()) {
                                    break;
                                }
                            }
                        }
                    }
                }
        }


        return $status;
    }


    /**
     *  Fires an event in the events manager causing the active listeners to be notified about it
     *
     *<code>
     *    $eventsManager->fire("db", $connection);
     *</code>
     * @param null|string $eventType
     * @param $source
     * @param null $data
     * @param bool $cancelable
     * @return mixed|null
     * @throws Exception
     */
    public function fire(?string $eventType, $source, $data = null, bool $cancelable = true)
    {

        $events = $this->_events;
        if (!is_array($events)) {
            return null;
        }

        // All valid events must have a colon separator
        if (!strpos($eventType, ":")) {
            throw new Exception("Invalid event type " . $eventType);
        }

        $eventParts = explode(":", $eventType);
        $type       = $eventParts[0];
        $eventName  = $eventParts[1];

        $status = null;

        // Responses must be traced?
        if ($this->_collect) {
            $this->_responses = null;
        }

        $event = null;

        // Check if events are grouped by type
        $fireEvents = $events[$type] ?? null;
        if ($fireEvents) {

            if (is_object($fireEvents) || is_array($fireEvents)) {

                // Create the event context
                $event = new Event($eventName, $source, $data, $cancelable);

                // Call the events queue
                $status = $this->fireQueue($fireEvents, $event);
            }
        }

        // Check if there are listeners for the event type itself
        $fireEvents = $events[$eventType] ?? null;
        if ($fireEvents ) {

            if (is_object($fireEvents) || is_array($fireEvents)) {

                // Create the event if it wasn't created before
                if ($event === null) {
                    $event = new Event($eventName, $source, $data, $cancelable);
                }

                // Call the events queue
                $status = $this->fireQueue($fireEvents, $event);
            }
        }

        return $status;
    }

    /**
     * Check whether certain type of event has listeners
     */
    public function hasListeners(?string $type): bool
    {
        return isset($this->_events[$type]);
    }

    /**
     * Returns all the attached listeners of a certain type
     *
     * @param string type
     * @return array
     */
    public function getListeners(?string $type)
    {
        $events = $this->_events;
        if (is_array($events)) {
            $fireEvents = $events[$type] ?? null;
            if ($fireEvents) {
                return $fireEvents;
            }
        }
        return [];
    }
}
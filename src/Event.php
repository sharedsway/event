<?php
/**
 * Created by PhpStorm.
 * User: debian
 * Date: 19-8-18
 * Time: 上午12:58
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

class Event implements EventInterface
{
    /**
     * Event type
     *
     * @var string
     */
    protected $_type;

    /**
     * Event source
     *
     * @var \object
     */
    protected $_source;

    /**
     * Event data
     *
     * @var mixed
     */
    protected $_data;

    /**
     * Is event propagation stopped?
     *
     * @var boolean
     */
    protected $_stopped = false;

    /**
     * Is event cancelable?
     *
     * @var boolean
     */
    protected $_cancelable = true;

    /**
     * Sharedsway\Event\Event constructor
     *
     * @param string type
     * @param \object source
     * @param mixed data
     * @param boolean cancelable
     */
    public function __construct(?string $type, $source, $data = null, bool $cancelable = true)
    {
        $this->_type   = $type;
        $this->_source = $source;

        if ($data !== null) {
            $this->_data = $data;
        }

        if ($cancelable !== true) {
            $this->_cancelable = $cancelable;
        }
    }


    /**
     * Sets event data.
     * @param null $data
     * @return EventInterface
     */
    public function setData($data = null): EventInterface
    {
        $this->_data = $data;

        return $this;
    }

    public function getData()
    {
        // TODO: Implement getData() method.
        return $this->_data;
    }

    /**
     * Sets event type.
     * @param null|string $type
     * @return EventInterface
     */
    public function setType(?string $type): EventInterface
    {
        $this->_type = $type;

        return $this;
    }


    public function getType()
    {
        return $this->_type;
    }


    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Stops the event preventing propagation.
     *
     * <code>
     * if( ($event->isCancelable()) ){
     *     $event->stop();
     * }
     * </code>
     * @return EventInterface
     * @throws Exception
     */
    public function stop(): EventInterface
    {
        if (!$this->_cancelable) {
            throw new Exception("Trying to cancel a non-cancelable event");
        }

        $this->_stopped = true;

        return $this;
    }

    /**
     * Check whether the event is currently stopped.
     */
    public function isStopped(): bool
    {
        return $this->_stopped;
    }

    /**
     * Check whether the event is cancelable.
     *
     * <code>
     * if( ($event->isCancelable()) ){
     *     $event->stop();
     * }
     * </code>
     */
    public function isCancelable(): bool
    {
        return $this->_cancelable;
    }
}

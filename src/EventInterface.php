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

/**
 * Sharedsway\Event\EventInterface
 *
 * Interface for Sharedsway\Event\Event class
 */
interface EventInterface
{
    /**
     * Gets event data
     */
    public function getData();

    /**
     * Sets event data
     * @param null $data
     * @return EventInterface
     */
	public function setData($data = null) : EventInterface;

	/**
     * Gets event type
     */
	public function getType();

    /**
     * Sets event type
     * @param null|string $type
     * @return EventInterface
     */
	public function setType(?string $type) :EventInterface ;


    public function getSource();


	/**
     * Stops the event preventing propagation
     */
	public function stop() :EventInterface;

	/**
     * Check whether the event is currently stopped
     */
	public function isStopped() :bool;

	/**
     * Check whether the event is cancelable
     */
	public function isCancelable() : bool;
}

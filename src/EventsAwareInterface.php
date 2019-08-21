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
use Sharedsway\Event\ManagerInterface;

interface EventsAwareInterface
{

    /**
     * Sets the events manager
     * @param \Sharedsway\Event\ManagerInterface $eventsManager
     * @return mixed
     */
    public function setEventsManager(ManagerInterface $eventsManager);

    /**
     * Returns the internal event manager
     * @return \Sharedsway\Event\ManagerInterface
     */
	public function getEventsManager() : ManagerInterface;

}

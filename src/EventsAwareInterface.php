<?php
/**
 * Created by PhpStorm.
 * User: debian
 * Date: 19-8-18
 * Time: 上午12:58
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

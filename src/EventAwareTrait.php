<?php
/**
 * Created by PhpStorm.
 * User: debian
 * Date: 19-8-21
 * Time: 上午9:41
 */


namespace Sharedsway\Event;



trait EventAwareTrait
{

    /**
     * Events Manager
     *
     * @var ManagerInterface
     */
    protected $_eventsManager;

    /**
     * Sets the event manager
     * @param ManagerInterface $eventsManager
     * @return mixed|void
     */
    public function setEventsManager(ManagerInterface $eventsManager)
    {
        $this->_eventsManager = $eventsManager;
    }

    /**
     * Returns the internal event manager
     */
    public function getEventsManager(): ManagerInterface
    {
        return $this->_eventsManager;
    }

}

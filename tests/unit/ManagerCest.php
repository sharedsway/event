<?php namespace Sharedsway\Test\Event;
use Codeception\Example;
use Sharedsway\Event\EventInterface;
use Sharedsway\Test\Event\UnitTester;
use Sharedsway\Event\Manager;
use Sharedsway\Event\Event;

class ManagerCest
{
    public function _before(UnitTester $I)
    {
    }

    // tests
    public function attachingListenersByEventNameAfterDetachingAll(UnitTester $I)
    {
        $first  = new FirstListener();
        $second = new SecondListener();

        $component = new ComponentX();

        $eventsManager = new Manager();
        $eventsManager->attach('log', $first);

        $component->setEventsManager($eventsManager);

        $logListeners = $component->getEventsManager()->getListeners('log');

        $I->assertCount(1,$logListeners);
        $I->assertInstanceOf(FirstListener::class,$logListeners[0]);

        $component->getEventsManager()->attach('log', $second);
        $logListeners = $component->getEventsManager()->getListeners('log');

        $I->assertCount(2,$logListeners);
        $I->assertInstanceOf(FirstListener::class, $logListeners[0]);
        $I->assertInstanceOf(SecondListener::class,$logListeners[1]);

        $component->getEventsManager()->detachAll('log');
        $logListeners = $component->getEventsManager()->getListeners('log');

        $I->assertEmpty($logListeners);

        $component->getEventsManager()->attach('log', $second);
        $logListeners = $component->getEventsManager()->getListeners('log');

        $I->assertCount(1,$logListeners);
        $I->assertInstanceOf(SecondListener::class, $logListeners[0]);
    }

    public function usingEvents(UnitTester $I)
    {
        $listener1 = new ThirdListener();
        $listener1->setTestCase($I);

        $listener2 = new ThirdListener();
        $listener2->setTestCase($I);

        $eventsManager = new Manager();
        $eventsManager->attach('dummy', $listener1);

        $componentX = new ComponentX();
        $componentX->setEventsManager($eventsManager);

        $componentY = new ComponentY();
        $componentY->setEventsManager($eventsManager);

        $componentX->leAction();
        $componentX->leAction();

        $componentY->leAction();
        $componentY->leAction();
        $componentY->leAction();

        $I->assertEquals(2,$listener1->getBeforeCount());
        $I->assertEquals(2,$listener1->getAfterCount());

        $eventsManager->attach('dummy', $listener2);

        $componentX->leAction();
        $componentX->leAction();

        $I->assertEquals(4,$listener1->getBeforeCount());
        $I->assertEquals(4,$listener1->getAfterCount());

        $I->assertEquals(2,$listener2->getBeforeCount());
        $I->assertEquals(2,$listener2->getAfterCount());

        //expect($this->listener)->same($listener2);

        $eventsManager->detach('dummy', $listener1);

        $componentX->leAction();
        $componentX->leAction();

        $I->assertEquals(4,$listener1->getBeforeCount());
        $I->assertEquals(4,$listener1->getAfterCount());

        $I->assertEquals(4,$listener2->getBeforeCount());
        $I->assertEquals(4,$listener2->getAfterCount());

    }

    public function usingEventsWithPriority(UnitTester $I)
    {
        $listener1 = new ThirdListener();
        $listener1->setTestCase($I);

        $listener2 = new ThirdListener();
        $listener2->setTestCase($I);

        $eventsManager = new Manager();
        $eventsManager->enablePriorities(true);

        $eventsManager->attach('dummy', $listener1, 100);

        $componentX = new ComponentX();
        $componentX->setEventsManager($eventsManager);



        $componentY = new ComponentY();
        $componentY->setEventsManager($eventsManager);

        $componentX->leAction();
        $componentX->leAction();

        $componentY->leAction();
        $componentY->leAction();
        $componentY->leAction();

        $I->assertEquals(2, $listener1->getBeforeCount());
        $I->assertEquals(2, $listener1->getAfterCount());

        $eventsManager->attach('dummy', $listener2, 150);

        $componentX->leAction();
        $componentX->leAction();

        $I->assertEquals(4, $listener1->getBeforeCount());
        $I->assertEquals(4, $listener1->getAfterCount());

        $I->assertEquals(2, $listener2->getBeforeCount());
        $I->assertEquals(2, $listener2->getAfterCount());

        //expect($this->listener)->same($listener1);

        $eventsManager->detach('dummy', $listener1);

        $componentX->leAction();
        $componentX->leAction();

        $I->assertEquals(4, $listener1->getBeforeCount());
        $I->assertEquals(4, $listener1->getAfterCount());

        $I->assertEquals(4, $listener2->getBeforeCount());
        $I->assertEquals(4, $listener2->getAfterCount());
    }

    public function stopEventsInEventsManager(UnitTester $I)
    {
        $number        = 0;
        $eventsManager = new Manager();

        $propagationListener = function (Event $event, $component, $data) use (&$number) {
            $number++;
            $event->stop();
        };

        $eventsManager->attach('some-type', $propagationListener);
        $eventsManager->attach('some-type', $propagationListener);

        $eventsManager->fire('some-type:beforeSome', $this);

        $I->assertEquals(1, $number);
    }

    public function attachAndFire(UnitTester $I)
    {
        $number        = 0;
        $eventsManager = new Manager();

        $propagationListener = function (Event $event) use (&$number) {
            $number++;
        };

        $eventsManager->attach('hello:world', $propagationListener);
        $eventsManager->fire('hello:world',$this);
        $eventsManager->fire('hello:php',$this);
        $I->assertEquals(1, $number);
        $eventsManager->fire('hello:world',$this);
        $I->assertEquals(2, $number);
    }

    /**
     * @param \Sharedsway\Test\Event\UnitTester $I
     * @param Example $example
     * @throws \Sharedsway\Event\Exception
     * @dataProvider ifEnablePriorities
     */
    public function detachClosureListener(UnitTester $I, Example $example)
    {
        $enablePriorities = $example[0];
        $manager = new Manager();
        $manager->enablePriorities($enablePriorities);

        $handler = function () {
            echo __METHOD__;
        };

        $manager->attach('test:detachable', $handler);
        $events = $this->getProtectedProperty($manager, '_events');

        $I->assertCount(1, $events);
        $I->assertArrayHasKey('test:detachable', $events);
        $I->assertCount(1, $events['test:detachable']);


        $manager->detach('test:detachable', $handler);

        $events = $this->getProtectedProperty($manager, '_events');

        //$events = $this->tester->getProtectedProperty($manager, '_events');

       // expect($events)->count(1);
        //expect(array_key_exists('test:detachable', $events))->true();
        //expect($events['test:detachable'])->count(0);
        $I->assertCount(1, $events);
        $I->assertArrayHasKey('test:detachable', $events);
        $I->assertCount(0, $events['test:detachable']);

    }

    protected function getProtectedProperty($object,$prop)
    {
        $reflection = new \ReflectionObject($object);
        $prop = $reflection->getProperty($prop);
        $prop->setAccessible(true);
        return $events = $prop->getValue($object);
    }

    /**
     * @param \Sharedsway\Test\Event\UnitTester $I
     * @param Example $example
     * @throws \Sharedsway\Event\Exception
     * @dataProvider ifEnablePriorities
     */
    public function detachObjectListener(UnitTester $I, Example $example)
    {
        $enablePriorities = $example[0];
        $manager = new Manager();
        $manager->enablePriorities($enablePriorities);

        $handler = new \stdClass();
        $manager->attach('test:detachable', $handler);
        //$events = $this->tester->getProtectedProperty($manager, '_events');
        $events = $this->getProtectedProperty($manager, '_events');

//        expect($events)->count(1);
//        expect(array_key_exists('test:detachable', $events))->true();
//        expect($events['test:detachable'])->count(1);
        $I->assertCount(1, $events);
        $I->assertArrayHasKey('test:detachable', $events);
        $I->assertCount(1, $events['test:detachable']);

        $manager->detach('test:detachable', $handler);

        //$events = $this->tester->getProtectedProperty($manager, '_events');
        $events = $this->getProtectedProperty($manager, '_events');

//        expect($events)->count(1);
//        expect(array_key_exists('test:detachable', $events))->true();
//        expect($events['test:detachable'])->count(0);
        $I->assertCount(1, $events);
        $I->assertArrayHasKey('test:detachable', $events);
        $I->assertCount(0, $events['test:detachable']);
    }

    protected function ifEnablePriorities()
    {
        return [
            [true],
            [false],
        ];
    }

}

class ComponentX
{
    /**
     * @var Manager
     */
    protected $eventsManager;

    public function setEventsManager(Manager $eventsManager)
    {
        $this->eventsManager = $eventsManager;
    }

    /**
     * @return Manager
     */
    public function getEventsManager()
    {
        return $this->eventsManager;
    }

    public function leAction()
    {
        $this->eventsManager->fire('dummy:beforeAction', $this, 'extra data');
        $this->eventsManager->fire('dummy:afterAction', $this, ['extra', 'data']);
    }
}


class ComponentY
{
    /**
     * @var Manager
     */
    protected $eventsManager;

    public function setEventsManager(Manager $eventsManager)
    {
        $this->eventsManager = $eventsManager;
    }

    /**
     * @return Manager
     */
    public function getEventsManager()
    {
        return $this->eventsManager;
    }

    public function leAction()
    {
        $this->eventsManager->fire('another:beforeAction', $this);
        $this->eventsManager->fire('another:afterAction', $this);
    }
}

class FirstListener
{
}

class SecondListener
{
}
class ThirdListener
{
    /** @var  UnitTester */
    protected $testCase;

    protected $before = 0;

    protected $after = 0;


    public function setTestCase(UnitTester $testCase)
    {
        $this->testCase = $testCase;
    }

    public function beforeAction($event, $component, $data)
    {
        var_dump('++++++ before +++++++++++');
        $this->testCase->assertInstanceOf(Event::class, $event);
        $this->testCase->assertInstanceOf(ComponentX::class, $component);
        $this->testCase->assertEquals($data, 'extra data');

        $this->before++;
    }

    public function afterAction(EventInterface $event, $component)
    {
        $this->testCase->assertInstanceOf(Event::class, $event);
        $this->testCase->assertInstanceOf(ComponentX::class, $component);
        $this->testCase->assertEquals($event->getData(), ['extra', 'data']);

        $this->after++;

        //$this->testCase->setLastListener($this);
    }

    public function getBeforeCount()
    {
        return $this->before;
    }

    public function getAfterCount()
    {
        return $this->after;
    }
}

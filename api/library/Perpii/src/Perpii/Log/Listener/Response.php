<?php
namespace Perpii\Log\Listener;

use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\CallbackHandler;
use Psr\Log\LoggerInterface as Log;

/**
 * Class Request
 *
 * @package Application\Event
 */
class Response implements ListenerAggregateInterface
{

    /**
     * @var Log
     */
    protected $log;

    /**
     * @var array
     */
    protected $listeners = array();

    /**
     * @param Log $log
     */
    public function __construct(Log $log = null)
    {
        if (!is_null($log)) {
            $this->setLog($log);
        }
    }

    /**
     * @return Log
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param Log $log
     *
     * @return Request
     */
    public function setLog(Log $log)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * @return array
     */
    public function getListeners()
    {
        return $this->listeners;
    }

    /**
     * @param CallbackHandler $listeners
     *
     * @return Request
     */
    public function addListener(CallbackHandler $listeners)
    {
        $this->listeners[] = $listeners;

        return $this;
    }

    /**
     * @param int $index
     *
     * @return bool
     */
    public function removeListener($index)
    {
        if (!empty($this->listeners[$index])) {
            unset($this->listeners[$index]);

            return true;
        }

        return false;
    }

    /**
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->addListener($events->attach(MvcEvent::EVENT_FINISH, array($this, 'logResponse')));
        //$this->addListener($events->attach(MvcEvent::EVENT_FINISH, array($this, 'shutdown'), -1000));
    }

    /**
     * @param EventManagerInterface $events
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->getListeners() as $index => $listener) {
            if ($events->detach($listener)) {
                $this->removeListener($index);
            }
        }
    }

    /**
     * @param EventInterface $event
     */
    public function logResponse(EventInterface $event)
    {
        if ($event->getRequest() instanceOf \Zend\Http\PhpEnvironment\Request) {
            $this->getLog()->debug(
                print_r(
                    array(
                            "\nResponse" => array(
                                "\nuri" => $event->getRequest()->getUri()->toString(),
                                "\nstatusCode" => $event->getResponse()->getStatusCode(),
                                "\ncontent" => $event->getResponse()->getContent()
                            )
                        )
                    ,
                    true
                )
            );
        }
    }

    /**
     * @param EventInterface $event
     */
    public function shutdown(EventInterface $event)
    {
        foreach ($this->getLog()->getWriters() as $writer) {
            $writer->shutdown();
        }
    }
}

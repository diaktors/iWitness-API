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
class Request implements ListenerAggregateInterface
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
        $this->addListener($events->attach(MvcEvent::EVENT_ROUTE, array($this, 'logRequest')));
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
    public function logRequest(EventInterface $event)
    {
        $request = $event->getRequest();
        if (($request) && $request instanceOf \Zend\Http\PhpEnvironment\Request) {
            $logger = $this->getLog();
            $logger->debug(
                print_r(
                    array(
                            "\nRequest" => $event->getRequest()->getUri()->toString(),
                            //'Method' => $event->getRequest()->getUri()->getMethod(),
                            //'Post' => $request->getPost(),
                            "\nContent" => $request->getContent(),
                            //'Header' => $request->getHeaders()->getArrayCopy()
                    )
                    ,
                    true
                )
            );
        }
    }
}

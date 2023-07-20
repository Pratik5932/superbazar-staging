<?php

namespace AstraPrefixed\GetAstra\Client\Helper;

use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Psr\Log\LoggerInterface;
use AstraPrefixed\Psr\SimpleCache\CacheInterface;
use AstraPrefixed\GetAstra\Client\Service\OAuthService;
/**
 * Description of QueueHelper class
 */
class QueueHelper
{
    private const EXECUTE_LIMIT = 1;
    //no of tasks that can be executed at a time
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var OauthService
     */
    private $oauthService;
    private $updateService;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var CacheInterface
     */
    private $options;
    //tasks types
    public const GK_UPDATE_TASK = 'handleGkUpdateTask';
    public const QUEUE_OPTION = 'queue';
    public const QUEUE_DONE_OPTION = 'queueDone';
    //Task structure
    // $task = [
    //handler => process
    //metadata => data
    //]
    //
    // metadata
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->oauthService = $container->get('oauth');
        $this->updateService = $container->get('update');
        $this->logger = $container->get('logger');
        $this->options = $container->get('options');
    }
    /**
     * checks the queue for any pending tasks
     */
    public function getQueue()
    {
        return $this->options->get(self::QUEUE_OPTION);
    }
    public function addTaskToQueue($task) : void
    {
        if ($this->isValidTask($task)) {
            $existing = $this->getQueue();
            $existing[] = $task;
            $this->options->set(self::QUEUE_OPTION, $existing);
        }
    }
    /**
     * starts executing the tasks in queue
     * respects the limit.
     * gets called from checks middleware
     */
    public function executeTask() : void
    {
        if ($this->isQueueEmpty()) {
            //$this->logger->debug('Queue empty'); //will spam sentry - @todo remove
            return;
        }
        $queue = $this->getQueue();
        for ($i = 0; $i < $this::EXECUTE_LIMIT && \count($queue) > 0; $i++) {
            $task = \array_shift($queue);
            if (isset($task['handler']) && isset($task['metadata'])) {
                $handlerClass = $this->getHandlersClass($task['handler']);
                if (!$handlerClass) {
                    $this->logger->error('No class found for the task with handler - ' . $task['handler']);
                    $task['result'] = 'CRITICAL - No handler found for this task.';
                } else {
                    //calling handler and saving its result in the task.
                    $task['result'] = \call_user_func([$handlerClass, $task['handler']], $task['metadata']);
                    $className = \get_class($handlerClass);
                    //$this->logger->error("Handle with name - {$className} called");
                }
            } else {
                $this->logger->warning('Invalid task in queue', ['task' => $task]);
                $task['result'] = 'Invalid task';
            }
            //saving the completed task in QueueDone.
            $completedTasks = $this->options->get(self::QUEUE_DONE_OPTION);
            $completedTasks[] = $task;
            $this->options->set(self::QUEUE_DONE_OPTION, $completedTasks);
        }
        $this->options->set(self::QUEUE_OPTION, $queue);
        return;
    }
    /**
     * Check if its a valid tasks
     * for now everythings valid.
     */
    public function isValidTask($task)
    {
        return \true;
    }
    /**
     * @return bool true if queue empty false otherwise
     */
    public function isQueueEmpty()
    {
        $queue = $this->getQueue();
        if (empty($queue)) {
            return \true;
        } else {
            if (\is_array($queue) && \count($queue) == 0) {
                return \true;
            } else {
                return \false;
            }
        }
    }
    /**
     * Helper function to get the class object for a given handler.
     * If no handler is found false is returned
     */
    private function getHandlersClass($handler)
    {
        if ($handler === self::GK_UPDATE_TASK) {
            return $this->updateService;
        }
        // if($handler === self::examplehandler){
        //     return $this->exampleService;
        // }
        return \false;
    }
}

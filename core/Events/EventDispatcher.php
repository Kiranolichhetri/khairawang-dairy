<?php

declare(strict_types=1);

namespace Core\Events;

/**
 * Event Dispatcher
 * 
 * Provides event registration, listener management, and dispatch capabilities.
 */
class EventDispatcher
{
    /** @var array<string, array<callable>> */
    private array $listeners = [];
    
    /** @var array<string, array<string>> */
    private array $wildcardListeners = [];
    
    /** @var bool */
    private bool $queueEnabled = false;
    
    /** @var array<array{event: string, payload: mixed}> */
    private array $queue = [];

    /**
     * Register an event listener
     */
    public function listen(string $event, callable $listener, int $priority = 0): void
    {
        // Check for wildcard
        if (str_contains($event, '*')) {
            $this->wildcardListeners[$event][] = [
                'listener' => $listener,
                'priority' => $priority,
            ];
            return;
        }
        
        $this->listeners[$event][] = [
            'listener' => $listener,
            'priority' => $priority,
        ];
        
        // Sort by priority (higher = first)
        usort($this->listeners[$event], fn($a, $b) => $b['priority'] <=> $a['priority']);
    }

    /**
     * Register multiple listeners for an event
     * 
     * @param array<string, callable|array<callable>> $events
     */
    public function subscribe(array $events): void
    {
        foreach ($events as $event => $listeners) {
            $listeners = is_array($listeners) ? $listeners : [$listeners];
            
            foreach ($listeners as $listener) {
                $this->listen($event, $listener);
            }
        }
    }

    /**
     * Dispatch an event
     * 
     * @return array<mixed> Array of listener return values
     */
    public function dispatch(string $event, mixed $payload = null): array
    {
        $results = [];
        
        // Get listeners for this event
        $listeners = $this->getListeners($event);
        
        foreach ($listeners as $listenerData) {
            $listener = $listenerData['listener'];
            $result = $listener($payload, $event);
            
            // Stop propagation if listener returns false
            if ($result === false) {
                break;
            }
            
            $results[] = $result;
        }
        
        return $results;
    }

    /**
     * Dispatch an event and queue it for async processing
     */
    public function dispatchQueued(string $event, mixed $payload = null): void
    {
        if ($this->queueEnabled) {
            $this->queue[] = [
                'event' => $event,
                'payload' => $payload,
            ];
        } else {
            $this->dispatch($event, $payload);
        }
    }

    /**
     * Process queued events
     */
    public function processQueue(): void
    {
        while (!empty($this->queue)) {
            $item = array_shift($this->queue);
            $this->dispatch($item['event'], $item['payload']);
        }
    }

    /**
     * Enable queue mode
     */
    public function enableQueue(): void
    {
        $this->queueEnabled = true;
    }

    /**
     * Disable queue mode
     */
    public function disableQueue(): void
    {
        $this->queueEnabled = false;
    }

    /**
     * Get all listeners for an event (including wildcards)
     * 
     * @return array<array{listener: callable, priority: int}>
     */
    public function getListeners(string $event): array
    {
        $listeners = $this->listeners[$event] ?? [];
        
        // Add wildcard listeners
        foreach ($this->wildcardListeners as $pattern => $wildcardListeners) {
            if ($this->matchesWildcard($event, $pattern)) {
                $listeners = array_merge($listeners, $wildcardListeners);
            }
        }
        
        // Sort by priority
        usort($listeners, fn($a, $b) => $b['priority'] <=> $a['priority']);
        
        return $listeners;
    }

    /**
     * Check if event matches wildcard pattern
     */
    private function matchesWildcard(string $event, string $pattern): bool
    {
        $pattern = preg_quote($pattern, '/');
        $pattern = str_replace('\*', '.*', $pattern);
        return preg_match('/^' . $pattern . '$/', $event) === 1;
    }

    /**
     * Check if event has listeners
     */
    public function hasListeners(string $event): bool
    {
        return !empty($this->getListeners($event));
    }

    /**
     * Remove a specific listener
     */
    public function forget(string $event, callable $listener = null): void
    {
        if ($listener === null) {
            unset($this->listeners[$event]);
            return;
        }
        
        if (!isset($this->listeners[$event])) {
            return;
        }
        
        $this->listeners[$event] = array_filter(
            $this->listeners[$event],
            fn($item) => $item['listener'] !== $listener
        );
    }

    /**
     * Remove all listeners
     */
    public function flush(): void
    {
        $this->listeners = [];
        $this->wildcardListeners = [];
    }

    /**
     * Register a subscriber class
     */
    public function addSubscriber(EventSubscriber $subscriber): void
    {
        $this->subscribe($subscriber->getSubscribedEvents());
    }

    /**
     * Dispatch event until a listener returns non-null
     */
    public function dispatchUntil(string $event, mixed $payload = null): mixed
    {
        $listeners = $this->getListeners($event);
        
        foreach ($listeners as $listenerData) {
            $listener = $listenerData['listener'];
            $result = $listener($payload, $event);
            
            if ($result !== null) {
                return $result;
            }
        }
        
        return null;
    }

    /**
     * Create an event object from class name
     */
    public static function createEvent(string $class, mixed ...$args): object
    {
        return new $class(...$args);
    }
}

/**
 * Event Subscriber Interface
 */
interface EventSubscriber
{
    /**
     * Get the events and their handlers
     * 
     * @return array<string, callable|array<callable>>
     */
    public function getSubscribedEvents(): array;
}

/**
 * Base Event Class
 */
abstract class Event
{
    private bool $propagationStopped = false;

    /**
     * Stop event propagation
     */
    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    /**
     * Check if propagation is stopped
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }
}

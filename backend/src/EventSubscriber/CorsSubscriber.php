<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class CorsSubscriber implements EventSubscriberInterface
{
    public function __construct(private string $corsAllowOrigin) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onRequest', 999], KernelEvents::RESPONSE => 'onResponse'];
    }

    public function onRequest(RequestEvent $event): void
    {
        if ($event->getRequest()->getMethod() === 'OPTIONS') $event->setResponse(new Response(status: 204));
    }

    public function onResponse(ResponseEvent $event): void
    {
        $headers = $event->getResponse()->headers;
        $headers->set('Access-Control-Allow-Origin', $this->corsAllowOrigin);
        $headers->set('Access-Control-Allow-Headers', 'Content-Type, X-Telemetry-Key, Authorization');
        $headers->set('Access-Control-Allow-Methods', 'GET, POST, PATCH, OPTIONS');
    }
}

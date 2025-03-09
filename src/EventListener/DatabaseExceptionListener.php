<?php

namespace App\EventListener;

use Doctrine\DBAL\Exception\ConnectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DatabaseExceptionListener implements EventSubscriberInterface
{
    private $urlGenerator;
    private $environment;

    public function __construct(UrlGeneratorInterface $urlGenerator, string $environment)
    {
        $this->urlGenerator = $urlGenerator;
        $this->environment = $environment;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        // Only handle database exceptions in production
        if ($this->environment !== 'prod') {
            return;
        }

        $exception = $event->getThrowable();
        $previous = $exception->getPrevious();

        // Check if this is a database connection exception
        if ($exception instanceof ConnectionException || 
            ($previous instanceof ConnectionException) ||
            (strpos($exception->getMessage(), 'database') !== false) ||
            (strpos($exception->getMessage(), 'Database') !== false) ||
            (strpos($exception->getMessage(), 'SQLSTATE') !== false)) {
            
            // Log the error
            error_log('Database connection error: ' . $exception->getMessage());
            
            // Don't redirect if we're already on the maintenance page
            $request = $event->getRequest();
            if ($request->attributes->get('_route') === 'app_maintenance') {
                return;
            }
            
            // Redirect to maintenance page
            $response = new RedirectResponse($this->urlGenerator->generate('app_maintenance'));
            $event->setResponse($response);
        }
    }
} 
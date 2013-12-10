<?php

namespace Auth\Listener;

use Symfony\Component\Security\Http\Firewall\ExceptionListener as SexeptionListener;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ExceptionListener extends SexeptionListener
{
    protected $app;

    public function __construct(\Silex\Application $app, $name, $entryPoint = null)
    {
        $this->app = $app;

        $app[$entryPoint = 'security.entry_point.'.$name.'.form'] = $app['security.entry_point.form._proto']($name, array());

        parent::__construct(
            $app['security'],
            $app['security.trust_resolver'],
            $app['security.http_utils'],
            $name,
            $app[$entryPoint],
            null, // errorPage
            new \Controllers\AccessDenied($app), // AccessDeniedHandlerInterface
            $app['logger']
        );
    }

    /**
     * Handles AccessDeniedException.
     *
     * @param GetResponseForExceptionEvent $event
     * @return \Controllers\RedirectResponse|\Symfony\Component\HttpFoundation\Response|void
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $request = $event->getRequest();
        $logger = $this->app['logger'];
        $accessDeniedHandler = new \Controllers\AccessDenied($this->app);

        // determine the actual cause for the exception
        while (null !== $previous = $exception->getPrevious()) {
            $exception = $previous;
        }

        if ($exception instanceof AccessDeniedException) {
            $event->setException(new AccessDeniedHttpException($exception->getMessage(), $exception));

            if (null !== $this->app['logger']) {
                $logger->debug(sprintf('Access is denied (and user is neither anonymous, nor remember-me) by "%s" at line %s', $exception->getFile(), $exception->getLine()));
            }

            try {
                if (null !== $accessDeniedHandler) {
                    return $accessDeniedHandler->handle($request, $exception);
                }
            } catch (\Exception $e) {
                if (null !== $logger) {
                    $logger->error(sprintf('Exception thrown when handling an exception (%s: %s)', get_class($e), $e->getMessage()));
                }

                $event->setException(new \RuntimeException('Exception thrown when handling an exception.', 0, $e));

                return;
            }
        }

        return parent::onKernelException($event);
    }
} 
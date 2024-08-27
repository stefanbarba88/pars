<?php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionSubscriber implements EventSubscriberInterface {
  private RouterInterface $router;

  public function __construct(RouterInterface $router) {
    $this->router = $router;
  }

  public function onKernelException(ExceptionEvent $event) {
    $exception = $event->getThrowable();

    if ($exception instanceof NotFoundHttpException) {
      $response = new RedirectResponse($this->router->generate('error_page'));
      $event->setResponse($response);
    }
  }

  public static function getSubscribedEvents() {
    return [
      KernelEvents::EXCEPTION => 'onKernelException',
    ];
  }
}

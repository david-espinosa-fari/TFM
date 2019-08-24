<?php


namespace App\Domain;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class RoutListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        // You get the exception object from the received event
        $exception = $event->getException();
        if ($exception instanceof HttpException)//esta es la excepcion de rout not found
        {
            /*$message = sprintf(
               'My Error says: %s with code: %s',
               $exception->getMessage(),
               $exception->getCode()
           );*/
            // Customize your response object to display the exception details
            //$response = new Response();
            $response = Response::create(
                'Message: ' . $exception->getMessage(), 404
            );

            //$response->setContent($message);

            // HttpExceptionInterface is a special type of exception that
            // holds status code and header details
            if ($exception instanceof HttpExceptionInterface) {

                $response->setStatusCode($exception->getStatusCode());
                $response->headers->replace(
                    array(
                        'Content-Type' => 'application/json',
                        'User-Agent' => 'MeteoSalleMiddel'
                    ));
            } else {
                $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // sends the modified response object to the event
            $event->setResponse($response);
        }
    }
}


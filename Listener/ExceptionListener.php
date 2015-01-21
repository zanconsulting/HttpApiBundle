<?php


namespace Zan\HttpApiBundle\Listener;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * Customized exception handling for API clients
 */
class ExceptionListener
{
    /**
     * @var bool
     */
    protected $includeTraceInResponse;

    function __construct($includeTraceInResponse)
    {
        $this->includeTraceInResponse = $includeTraceInResponse;
    }


    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // You get the exception object from the received event
        $exception = $event->getException();

        $responseData = array(
            'success' => false,
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
        );

        if ($this->includeTraceInResponse) {
            $responseData['trace'] = $exception->getTraceAsString();
        }

        $serialized = json_encode($responseData);

        $response = new Response($serialized);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(500);

        // Send the modified response object to the event
        $event->setResponse($response);
    }
}
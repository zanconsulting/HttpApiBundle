<?php


namespace Zan\HttpApiBundle\Listener;


use JMS\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Zan\HttpApiBundle\Controller\UnserializedResponseController;

/**
 * Listens for incoming requests and checks if their controllers return unserialized
 * responses. If so, they are added to an array and then the responses are encoded.
 */
class EncodingResponseListener
{
    /**
     * @var array
     */
    protected $requestsToEncodeResponsesFor;

    /**
     * @var Serializer
     */
    protected $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;

        $this->requestsToEncodeResponsesFor = array();
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        /*
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }

        $controller = $controller[0];

        // We only operate on controllers that return responses needing
        // serialization
        if (! $controller instanceof UnserializedResponseController) {
            return;
        }

        // ------------------------------------------------------------
        // Decodes json-encoded post bodies and sets request parameters from the values
        $request = $event->getRequest();

        $reqMethod = $request->getMethod();

        // If the request method is "POST" without files, check for json and decode the response
        if ($reqMethod == "POST" && count($request->files->all()) == 0) {
            $decoded = json_decode($request->getContent(), true);
            if ($decoded !== null) {
                // Update the values of the original request so they appear as POST values
                foreach ($decoded as $key => $value) {
                    $request->request->set($key, $value);
                }
            }
        }

        $this->requestsToEncodeResponsesFor[] = $event->getRequest();
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
        $controllerResult = $event->getControllerResult();


        // Request was not to a controller that we handle responses for, so ignore
        if (!in_array($request, $this->requestsToEncodeResponsesFor)) {
            return;
        }

        $rawResponse = array(
            'success' => true,
            'data' => $controllerResult
        );
        $serialized = $this->serializer->serialize($rawResponse, 'json');

        $response = new Response($serialized);
        $response->headers->set('Content-Type', 'application/json');

        $event->setResponse($response);

        return $response;
    }
}
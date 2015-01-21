<?php


namespace Zan\HttpApiBundle\Exception;


use Exception;

class AuthenticationFailedException extends ZanHttpApiException
{
    public function __construct($message = null, $code = '', Exception $previous = null)
    {
        if (null === $message) {
            $message = 'Authentication Failed';
        }

        parent::__construct($message, $code, $previous);
    }
}
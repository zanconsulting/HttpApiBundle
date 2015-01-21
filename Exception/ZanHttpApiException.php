<?php


namespace Zan\HttpApiBundle\Exception;


use Exception;

class ZanHttpApiException extends \Exception
{
    public function __construct($message = "", $code = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->code = $code;
    }

}
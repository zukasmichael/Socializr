<?php
namespace AppException;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResourceNotFound extends NotFoundHttpException
{
    /**
     * Constructor.
     *
     * @param string     $message  The internal exception message
     * @param \Exception $previous The previous exception
     * @param integer    $code     The internal exception code
     */
    public function __construct($message = 'The requested resource was not found.', \Exception $previous = null, $code = 0)
    {
        parent::__construct($message, $previous, $code);
    }
} 
<?php
namespace AppException;

use Symfony\Component\HttpKernel\Exception\HttpException;

class AccessDenied extends HttpException
{
    /**
     * Constructor.
     *
     * @param string     $message  The internal exception message
     * @param \Exception $previous The previous exception
     * @param integer    $code     The internal exception code
     */
    public function __construct($message = 'Access to this resource is forbidden.', \Exception $previous = null, $code = 0)
    {
        parent::__construct(403, $message, $previous, array(), $code);
    }
} 
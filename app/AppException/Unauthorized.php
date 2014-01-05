<?php
namespace AppException;

use Symfony\Component\HttpKernel\Exception\HttpException;

class Unauthorized extends HttpException
{
    /**
     * Constructor.
     *
     * @param string     $message  The internal exception message
     * @param \Exception $previous The previous exception
     * @param integer    $code     The internal exception code
     */
    public function __construct($message = 'You cannot be authorized.', \Exception $previous = null, $code = 0)
    {
        parent::__construct(401, $message, $previous, array(), $code);
    }
} 
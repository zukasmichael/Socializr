<?php

namespace Controllers;

use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;


class AccessDenied implements AccessDeniedHandlerInterface
{
    protected $app;

    public function __construct(\Silex\Application $app)
    {
        $this->app = $app;
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        new Response(json_encode(array('status' => 'protected')), 403);
    }
}
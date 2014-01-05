<?php

namespace Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Models\User as User;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\SessionCsrfProvider;
use Symfony\Component\HttpFoundation\Request;

class Account
{
    /**
     * @var \Silex\Application
     */
    protected $app;

    /**
     * @var \Symfony\Component\Form\Extension\Csrf\CsrfProvider\SessionCsrfProvider
     */
    protected $csrfProvider;

    /**
     * @var \Symfony\Component\Routing\Generator\UrlGenerator
     */
    protected $urlGenerator;

    /**
     * @var array
     */
    protected $oauthServices;

    /**
     * @var \Models\User
     */
    protected $user;

    /**
     * Constructor for the account controllers
     *
     * @param \Silex\Application $app
     */
    public function __construct(\Silex\Application $app)
    {
        $this->app = $app;
        $this->csrfProvider = $app['form.csrf_provider'];
        $this->urlGenerator = $app['url_generator'];
        $this->oauthServices = $app['oauth.services'];
        $this->user = $app['user'];
    }

    /**
     * Login a user or return login url's in json
     *
     * @return JsonResponse
     */
    public function loginAction()
    {
        $services = array_keys($this->oauthServices);
        $jsonResponse = new \stdClass();

        if ($this->user !== null)
        {
            if ($_SERVER['is_installation']) {
                $jsonResponse->loggedin = true;
                return new JsonResponse($jsonResponse);
            }

            $locationHeader = "Location: https://socializr.io/#/user/profile";
            if ($this->app['test'] === true) {
                $locationHeader = "Location: http://test.socializr.io/";
            }
            header($locationHeader);
            exit;
        }
        else
        {
            $jsonResponse->loginPaths = array_map(function ($service) {
                return $this->urlGenerator->generate('_auth_service', array(
                    'service' => $service,
                    '_csrf_token' => $this->csrfProvider->generateCsrfToken('oauth')
                ));
            }, array_combine($services, $services));
        }

        return new JsonResponse($jsonResponse);
    }

    /**
     * Give a login error
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function loginFailedAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            header("Location: https://socializr.io/#/home?apimsguri=/loginfailed");
            exit;
        }
        $jsonResponse = new \stdClass();
        $jsonResponse->text = 'Login failed, try again';
        $jsonResponse->loginUrl = '/login';
        return new JsonResponse($jsonResponse);
    }

    /**
     * Give an accountDisabled error
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function accountDisabledAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            header("Location: https://socializr.io/#/home?apimsguri=/accountdisabled");
            exit;
        }
        $jsonResponse = new \stdClass();
        $jsonResponse->text = 'Login failed, your account is disabled.';
        $jsonResponse->loginUrl = '/login';
        return new JsonResponse($jsonResponse);
    }
}
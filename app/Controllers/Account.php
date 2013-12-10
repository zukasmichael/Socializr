<?php

namespace Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Gigablah\Silex\OAuth\Security\User\StubUser as User;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\SessionCsrfProvider;

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
     * @var \Gigablah\Silex\OAuth\Security\User\StubUser
     */
    protected $user;

    /**
     * Constructor for the account controllers
     *
     * @param SessionCsrfProvider $csrfProvider
     * @param UrlGenerator $urlGenerator
     * @param array $oauthServices
     * @param User $user
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
            header("Location: https://socializr.io/#/user/profile");
            exit;
//            $jsonResponse->text = sprintf('Hello %s! Your email is %s.', $this->user->getUsername(), $this->user->getEmail());
//            $jsonResponse->logoutUrl = $this->urlGenerator->generate('logout', [
//                '_csrf_token' => $this->csrfProvider->generateCsrfToken('logout')
//            ]);
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
     * @return JsonResponse
     */
    public function loginFailedAction()
    {
        $jsonResponse = new \stdClass();
        $jsonResponse->text = 'Login failed, try again';
        $jsonResponse->loginUrl = '/login';
        return new JsonResponse($jsonResponse);
    }
}
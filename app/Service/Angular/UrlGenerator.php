<?php

namespace Service\Angular;

use Symfony\Component\Routing\Generator\UrlGenerator as SymfonyUrlGenerator;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Psr\Log\LoggerInterface;

class UrlGenerator extends SymfonyUrlGenerator
{
    /**
     * This array defines the characters (besides alphanumeric ones) that will not be percent-encoded in the path segment of the generated URL.
     *
     * PHP's rawurlencode() encodes all chars except "a-zA-Z0-9-._~" according to RFC 3986. But we want to allow some chars
     * to be used in their literal form (reasons below). Other chars inside the path must of course be encoded, e.g.
     * "?" and "#" (would be interpreted wrongly as query and fragment identifier),
     * "'" and """ (are used as delimiters in HTML).
     */
    protected $decodedChars = array(
        // the slash can be used to designate a hierarchical structure and we want allow using it with this meaning
        // some webservers don't allow the slash in encoded form in the path for security reasons anyway
        // see http://stackoverflow.com/questions/4069002/http-400-if-2f-part-of-get-url-in-jboss
        '%2F' => '/',
        // the following chars are general delimiters in the URI specification but have only special meaning in the authority component
        // so they can safely be used in the path in unencoded form
        '%40' => '@',
        '%3A' => ':',
        // these chars are only sub-delimiters that have no predefined meaning and can therefore be used literally
        // so URI producing applications can use these chars to delimit subcomponents in a path segment without being encoded for better readability
        '%3B' => ';',
        '%2C' => ',',
        '%3D' => '=',
        '%2B' => '+',
        '%21' => '!',
        '%2A' => '*',
        '%7C' => '|',
        // Add the # to the decoded chars for Angular routing with pound sign
        '%23' => '#',
    );

    protected $clientHost;
    protected $clientScheme;

    /**
     * Constructor.
     *
     * @param LoggerInterface|null $logger  A logger instance
     */
    public function __construct($clientHost = 'socializr.io', $clientScheme = 'https', LoggerInterface $logger = null)
    {
        $this->clientHost = $clientHost;
        $this->clientScheme = $clientScheme;

        parent::__construct($this->generateRouteCollection(), $this->generateRequestContext(), $logger);
    }

    /**
     * @return RouteCollection
     */
    protected function generateRouteCollection()
    {
        $routes = new RouteCollection();

        $routes->add(
            'home',
            (new Route('/#/home'))
                ->setMethods('GET')
        );

        $routes->add(
            'groupDetails',
            (new Route('/#/groups/{id}'))
            ->setRequirement('id', '[0-9a-z]+')
            ->setMethods('GET')
        );

        $routes->add(
            'groups',
            (new Route('/#/groups'))
                ->setMethods('GET')
        );

        return $routes;
    }

    protected function generateRequestContext()
    {
        $context = new RequestContext();
        $context->setHost($this->clientHost);
        $context->setScheme($this->clientScheme);
        $context->setHttpPort(80);
        $context->setHttpsPort(443);
        $context->setQueryString('');

        return $context;
    }
} 
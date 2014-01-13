<?php
/**
 * Created by PhpStorm.
 * User: Sander en Dorien
 * Date: 12-1-14
 * Time: 18:39
 */

namespace Controllers;

use Models\Permission;
use Models\Group;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use AppException\AccessDenied;
use AppException\ResourceNotFound;
use AppException\ModelInvalid;
use Service\Queue\Invite as InviteService;
use Twitter\TwitterAPIExchange;

class TwitterProvider extends AbstractProvider {

    /**
     * @param Application $app
     * @return \Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        $controllers = parent::connect($app);

        $controllers->get('/', function (Request $request) use ($app) {
            throw new NotImplementedException('This is not implemented and I guess not needed!');
        });
        /**
         * Get groups
         */
        $controllers->get('/{hashtag}', function ($hashtag) use ($app) {
            $settings = array(
                'oauth_access_token' => $app['login.providers']['twitter']['oauth_access_token'],
                'oauth_access_token_secret' => $app['login.providers']['twitter']['oauth_access_token_secret'],
                'consumer_key' => $app['login.providers']['twitter']['API_KEY'],
                'consumer_secret' => $app['login.providers']['twitter']['API_SECRET']
            );
            $url = 'https://api.twitter.com/1.1/search/tweets.json';
            $getfield = '?q=#'.$hashtag.'&count=2';
            $requestMethod = 'GET';
            $twitter = new TwitterAPIExchange($settings);
            $feed = $twitter->setGetfield($getfield)
                ->buildOauth($url, $requestMethod)
                ->performRequest();

            $json_output = json_decode($feed, true);
            $tweets = array();
            foreach($json_output as $timeline) {
               // $tweets[] = $timeline->text;
            }
            return $this->getJsonResponseAndSerialize($json_output, 200, 'twitter-feed');
        })->assert('hashtag', '[0-9a-z]+')->bind('twitterFeed');

        return $controllers;
    }
} 
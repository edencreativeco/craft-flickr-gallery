<?php

namespace edencreative\craftflickrgallery\controllers;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use edencreative\craftflickrgallery\Plugin;
use edencreative\craftflickrgallery\base\FlickrServer;
use edencreative\craftflickrgallery\models\Token;
use edencreative\craftflickrgallery\services\TokensService;
use yii\web\Response;


/**
 * OAuth Controller
 */
class OauthController extends Controller {

    /**
     * Connect
     * 
     * @return Response
     * TODO - error handling / throws
     */
    public function actionConnect(): Response {

        $settings = Plugin::$plugin->getSettings();

        $flickr = new FlickrServer([
            'identifier' => $settings->flickrApiKey,
            'secret' => $settings->flickrApiSecret,
        ]);

        // Get a temporary token
        $temporaryCredentials = $flickr->getTemporaryCredentials();
        
        // Store temporary credentials in session
        Craft::$app->getSession()->set('flickr.oauth_temp_credentials', serialize($temporaryCredentials));

        // Redirect user to authorize URL
        $authorizationUrl = $flickr->getAuthorizationUrl($temporaryCredentials);
        $authorizationUrl .= "&perms=read";
        return $this->redirect($authorizationUrl);
    }

    /**
     * Callback
     * 
     * @return Response
     * TODO - error handling / throws
     */
    public function actionCallback(): Response {
        
        $settings = Plugin::$plugin->getSettings();

        $flickr = new FlickrServer([
            'identifier' => $settings->flickrApiKey,
            'secret' => $settings->flickrApiSecret,
        ]);

        // Retrieve temporary credentials from session
        $temporaryCredentials = unserialize(Craft::$app->getSession()->get('flickr.oauth_temp_credentials'));

        // Retrieve the verifier from the callback URL
        $verifier = Craft::$app->getRequest()->getQueryParam('oauth_verifier');
        $identifier = Craft::$app->getRequest()->getQueryParam('oauth_token');

        // Obtain token credentials
        $tokenCredentials = $flickr->getTokenCredentials($temporaryCredentials, $identifier, $verifier);


        // Save these for future requests
        $ts = new TokensService();
        $username = $settings->flickrUsername;
        $token = $ts->getToken($settings->flickrUsername);

        if (!$token) {
            $token = new Token([
                'username' => $username
            ]);
        }

        $token->token = $tokenCredentials->getIdentifier();
        $token->secret = $tokenCredentials->getSecret();

        $ts->saveToken($token);

        // set notice
        Craft::$app->getSession()->setNotice('Successfully connected to Flickr!');

        $redirect = UrlHelper::cpUrl('settings/plugins/craft-flickr-gallery');
        return $this->redirect($redirect);

    }

    /**
     * Disconnect
     * 
     * @return Response
     */
    public function actionDisconnect(): Response {

        $username = Craft::$app->getRequest()->getBodyParam('username');
        $redirect = UrlHelper::cpUrl('settings/plugins/craft-flickr-gallery');

        // if (!$username) {
        //     Craft::$app->getSession()->setError('Username not provided.');

        //     // redirect to settings
        //     return $this->redirect($redirect);
        // }
    

        // delete oauth token
        $ts = new TokensService();
        $token = $ts->getToken($username);

        if ($token) {
            $success = $ts->deleteTokenById($token->id);

            if (!$success) {
                Craft::$app->getSession()->setError('An error occurred.');

                // redirect to settings
                return $this->redirect($redirect);
            }
        }

        // set notice
        Craft::$app->getSession()->setNotice('Disconnected');

        // redirect to settings
        return $this->redirect($redirect);

    }

}

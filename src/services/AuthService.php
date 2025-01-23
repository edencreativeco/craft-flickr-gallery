<?php
    
namespace edencreative\craftflickrgallery\services;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;
use edencreative\craftflickrgallery\Plugin;
use edencreative\craftflickrgallery\base\FlickrServer;
use League\OAuth1\Client\Credentials\TokenCredentials;

class AuthService extends Component {

    public function getOauthCredentials() {
        $tokenCredentials = new TokenCredentials();
        $tokenCredentials->setIdentifier(Craft::$app->getSession()->get('flickr_token'));
        $tokenCredentials->setSecret(Craft::$app->getSession()->get('flickr_token_secret'));
    }

    public function setOauthCredentials(TokenCredentials $tokenCredentials) {
        Craft::$app->getSession()->set('flickr_token', $tokenCredentials->getIdentifier());
        Craft::$app->getSession()->set('flickr_token_secret', $tokenCredentials->getSecret());
    }


    /**
     * 
     */
    public function getAuthorizationUrl(): string {
        $settings = Plugin::$plugin->getSettings();

        $flickr = new FlickrServer([
            'identifier' => $settings->flickrApiKey,
            'secret' => $settings->flickrApiSecret,
        ]);


        // Start the OAuth process
        $temporaryCredentials = $flickr->getTemporaryCredentials();
        $authorizationUrl = $flickr->getAuthorizationUrl($temporaryCredentials);

        // Store temporary credentials in session
        Craft::$app->getSession()->set('flickr.oauth_temp_credentials', serialize($temporaryCredentials));


        return $authorizationUrl;
    }

    /**
     * @return  string
     */
    public function getCallbackUrl(): string {

        $settings = Plugin::$plugin->getSettings();
        if ($settings->callbackUrl) {
            // remove trailing slash if present
            return rtrim($settings->callbackUrl, '/') . "/admin/actions/craft-flickr-gallery/oauth/callback";
        } else {
            return UrlHelper::cpUrl('actions/craft-flickr-gallery/oauth/callback');
        }
    }


}
<?php

namespace edencreative\craftflickrgallery\base;

use edencreative\craftflickrgallery\services\AuthService;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Server\Server;
use League\OAuth1\Client\Server\User;
use League\OAuth1\Client\Signature\SignatureInterface;

class FlickrServer extends Server
{
    /**
     * Access token.
     *
     * @var string
     */
    protected $accessToken;

    /**
     * Application expiration.
     *
     * @var string
     */
    protected $applicationExpiration;

    /**
     * Application key.
     *
     * @var string
     */
    protected $applicationKey;

    /**
     * Application secret.
     *
     * @var string
     */
    protected $applicationSecret;

    /**
     * Application Callback Uri.
     *
     * @var string
     */
    protected $callbackUri;

    /**
     * Application name.
     *
     * @var string
     */
    protected $applicationName;

    /**
     * Application scope.
     *
     * @var string
     */
    protected $applicationScope;

    /**
     * @inheritDoc
     */
    public function __construct($clientCredentials, SignatureInterface $signature = null)
    {
        parent::__construct($clientCredentials, $signature);

        $authService = new AuthService();

        $this->callbackUri = $authService->getCallbackUrl();

        if (is_array($clientCredentials)) {
            $this->parseConfiguration($clientCredentials);
        }
    }

    /**
     * Set the access token.
     *
     * @param string $accessToken
     *
     * @return FlickrServer
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Set the application expiration.
     *
     * @param string $applicationExpiration
     *
     * @return FlickrServer
     */
    public function setApplicationExpiration($applicationExpiration)
    {
        $this->applicationExpiration = $applicationExpiration;

        return $this;
    }

    /**
     * Get application expiration.
     *
     * @return string
     */
    public function getApplicationExpiration()
    {
        return $this->applicationExpiration ?: '1day';
    }

    /**
     * Set the application name.
     *
     * @param string $applicationName
     *
     * @return FlickrServer
     */
    public function setApplicationName($applicationName)
    {
        $this->applicationName = $applicationName;

        return $this;
    }

    /**
     * Get application name.
     *
     * @return string|null
     */
    public function getApplicationName()
    {
        return $this->applicationName ?: null;
    }

    /**
     * Set the application scope.
     *
     * @param string $applicationScope
     *
     * @return FlickrServer
     */
    public function setApplicationScope($applicationScope)
    {
        $this->applicationScope = $applicationScope;

        return $this;
    }

    /**
     * Get application scope.
     *
     * @return string
     */
    public function getApplicationScope()
    {
        return $this->applicationScope ?: 'read';
    }

    /**
     * @inheritDoc
     */
    public function urlTemporaryCredentials()
    {
        return 'https://www.flickr.com/services/oauth/request_token';
    }

    /**
     * @inheritDoc
     */
    public function urlAuthorization()
    {
        return 'https://www.flickr.com/services/oauth/authorize?';
    }

    /**
     * @inheritDoc
     */
    public function urlTokenCredentials()
    {
        return 'https://www.flickr.com/services/oauth/access_token';
    }

    /**
     * @inheritDoc
     */
    public function urlUserDetails()
    {
        // Flickr does not have a specific endpoint for user details in OAuth1.
        return 'https://www.flickr.com/services/rest/?method=flickr.test.login&format=json&nojsoncallback=1';
    }

    /**
     * Parses the user details from the API response.
     *
     * @param mixed $data
     * @param TokenCredentials $tokenCredentials
     * @return \League\OAuth1\Client\Server\User
     */
    public function userDetails($data, TokenCredentials $tokenCredentials)
    {
        $user = new User();

        $user->uid = $data['user']['id'] ?? null;
        $user->nickname = $data['user']['username']['_content'] ?? null;
        $user->name = $data['user']['realname']['_content'] ?? null;

        $user->extra = (array)$data;

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function userUid($data, TokenCredentials $tokenCredentials)
    {
        return $data['user']['id'] ?? '';
    }

    /**
     * @inheritDoc
     */
    public function userEmail($data, TokenCredentials $tokenCredentials)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function userScreenName($data, TokenCredentials $tokenCredentials)
    {
        return $data['user']['username'] ?? '';
    }


    /**
     * Returns any extra parameters needed for token credentials requests.
     *
     * @return array
     */
    public function getTokenCredentialsHeaders()
    {
        return [];
    }

    // /**
    //  * Build authorization query parameters.
    //  *
    //  * @return string
    //  */
    // private function buildAuthorizationQueryParameters()
    // {
    //     $params = [
    //         'response_type' => 'fragment',
    //         'scope' => $this->getApplicationScope(),
    //         'expiration' => $this->getApplicationExpiration(),
    //         'name' => $this->getApplicationName(),
    //     ];

    //     return http_build_query($params);
    // }

    /**
     * Parse configuration array to set attributes.
     *
     * @param array $configuration
     *
     * @return void
     */
    private function parseConfiguration(array $configuration = [])
    {
        $configToPropertyMap = [
            'identifier' => 'applicationKey',
            'secret' => 'applicationSecret',
            'callback_uri' => 'callbackUri',
            'expiration' => 'applicationExpiration',
        ];

        foreach ($configToPropertyMap as $config => $property) {
            if (isset($configuration[$config])) {
                $this->$property = $configuration[$config];
            }
        }
    }
}

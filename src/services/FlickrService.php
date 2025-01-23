<?php
    
namespace edencreative\craftflickrgallery\services;

use Craft;
use craft\base\Component;
use edencreative\craftflickrgallery\elements\FlickrAsset;
use edencreative\craftflickrgallery\Plugin;
use League\OAuth1\Client\Credentials\TokenCredentials;

class FlickrService extends Component {

    const BASE_URL = "https://api.flickr.com/services/rest"; 
    const PHOTO_SIZES = [
        's' => 75,
        't' => 100,
        'q' => 150,
        'm' => 240,
        'w' => 400,
        'z' => 640,
        'b' => 1024
    ];


    //=================================================//
    //================= API Endpoints =================//
    //=================================================//
    
    /**
     * Get list of photos
     * @param   array   $options    optional properties: size, page, perpage, 
     * @return  ?object
     */
    public function getPhotos(array $options = []): ?object {
        
        $extras = $this->getExtrasParam($options['extras'] ?? null);
        
        $results = $this->fetch('people.getPhotos', [
            'user_id' => 'me',
            'per_page' => $options['perpage'] ?? $options['per_page'] ?? 20,
            'page' => $options['page'] ?? 1,
            'extras' => $extras,
        ]);

        $size = $options['size'] ?? null;

        // Add urls to each photo
        if (!empty($results?->photos->photo)) {
            $results->photos->photo = array_map(function($photo) use ($size) {
                $photo->original = $photo->url_o ?? null;
                return $this->photoDataResource($photo, $size);
            }, $results->photos->photo);
        }


        return $results;
    }


    /**
     * Get info for a photo by its id
     * @param   int     $id
     * @return  ?object
     */
    public function getPhotoInfo(int $id): ?object {

        $results = $this->fetch('photos.getInfo', [
            'photo_id' => $id
        ]);

        $photo = $results?->photo;

        if (!$photo) return null;

        // generate original from data

        $id = $photo->id;
        $serverId = $photo->server;
        $originalsecret = $photo->originalsecret;
        $originalformat = $photo->originalformat;

        $original = "https://live.staticflickr.com/$serverId/$id" . "_" . $originalsecret . "_o.$originalformat";
        $results->photo->original = $original;
        
        
        return $this->photoDataResource($results->photo);        
    }

    /**
     * Get all sizes for a photo by its id (including original)
     * @param   int     $id
     * @param   array   $options
     * @return  ?object
     */
    public function getPhotoSizes(int $id, array $options = []): ?object {


        $results = $this->fetch('photos.getSizes', [
            'photo_id' => $id,
        ]);

        return $results?->sizes?->size ?? null;
        // return $this->photoDataResource($results->photo);        
    }


    /**
     * Get Albums
     * @param   array   $options    optional properties: page, perpage, 
     * @return  ?object
     */
    public function getPhotosets(array  $options = []): ?object {
        
        $results = $this->fetch('photosets.getList', [
            'per_page' => $options['perpage'] ?? $options['per_page'] ?? 20,
            'page' => $options['page'] ?? 1,
        ]);

        // Add cover photo urls to each photoset
        if (!empty($results?->photosets->photoset)) {
            $results->photosets->photoset = array_map(function($photoset) {
                $photoset->cover = $this->getCoverImageUrl($photoset);
                return $photoset;
            }, $results->photosets->photoset);
        }


        return $results;
    }

    /**
     * Get a single album, with photos
     * @param   int     $id
     * @param   array   $options    optional properties: size, page, perpage, 
     * @return  ?object
     */
    public function getPhotoset(int $id, array $options = []): ?object {

        $extras = $this->getExtrasParam($options['extras'] ?? null);
        
        $results = $this->fetch('photosets.getPhotos', [
            'photoset_id' => $id,
            'per_page' => $options['perpage'] ?? $options['per_page'] ?? 20,
            'page' => $options['page'] ?? 1,
            'extras' => $extras,
        ]);

        // Plugin::info(json_encode($results));

        $size = $options['size'] ?? null;
        if (!empty($results?->photoset->photo)) {
            $results->photoset->photo = array_map(function($photo) use ($size) {
                $photo->original = $photo->url_o ?? null;
                return $this->photoDataResource($photo, $size);
            }, $results->photoset->photo);
        }

        return $results;
    }

    /**
     * Get a single album, with album details
     * @param   int     $id
     * @param   ?string $size   (optional)
     * @return  ?object
     */
    public function getPhotosetDetails(int $id): ?object {
        
        $results = $this->fetch('photosets.getInfo', [
            'photoset_id' => $id
        ]);

        return $results;
    }



    //=================================================//
    //================= Other Methods =================//
    //=================================================//
    

    /**
     * Supply an array of photos received from the flickr api, and add 'isImported' identifier on any we already have
     * @param   object[]    $photos
     * @return  array
     */
    public function identifyImportedPhotos(array $photos): array {
        
        $photoIds = array_map(fn($photo) => $photo->id, $photos);

        $existing = FlickrAsset::find()
            ->flickrPhotoId($photoIds)
            ->select(['flickr_photo_id'])
            ->asArray()
            ->column();

        // Organize into easily findable keys. Use underscore to avoid interpreting these numerically.
        $existingKeys = [];
        foreach($existing as $id) {
            $existingKeys["_$id"] = true;
        }

        $updatedPhotos = array_map(function($row) use ($existingKeys) {
            $row->isImported = $existingKeys["_$row->id"] ?? false;
            return $row;
        }, $photos);

        return $updatedPhotos;

    }
    
    
    //=================================================//
    //=================================================//


    /**
     * Get Image URL from photo data
     * @param   object  $data   include properties: id, farm, server, secret
     * @param   ?string $size
     * @return  string
     */
    public function getImageUrl(object $data, ?string $size = null): string {
        if ($size == 'original' && !empty($data->original)) return $data->original;

        $id = $data->id;
        $farmId = $data->farm;
        $serverId = $data->server;
        $secret = $data->secret;

        $url = "https://live.staticflickr.com/$serverId/$id" . "_" . $secret;
        // $url = "https://farm$farmId.staticflickr.com/$serverId/$id" . "_" . $secret;

        if ($size && in_array($size, array_keys(self::PHOTO_SIZES))) {
            $url .= "_$size";
        }

        $url .= ".jpg";

        return $url;
    }

    /**
     * Get the cover image for a photoset
     * @param   object  $data   include properties: id, farm, server, secret
     * @param   ?string $size
     * @return  string
     */
    public function getCoverImageUrl(object $data, ?string $size = 'q'): string {
        $id = $data->primary;
        $serverId = $data->server;
        $secret = $data->secret;

        $url = "https://live.staticflickr.com/$serverId/$id" . "_" . "$secret";

        if ($size && in_array($size, array_keys(self::PHOTO_SIZES))) {
            $url .= "_$size";
        }

        $url .= ".jpg";

        return $url;

    }


    /**
     * Get All Photo Sizes
     * @param   object  $data   include properties: id, farm, server, secret
     * @return  array
     */
    public function getAllPhotoSizes(object $data): array {

        $urls = [];

        foreach(self::PHOTO_SIZES as $key => $width) {
            $urls[$key] = $this->getImageUrl($data, $key);
        }

        $urls['original'] = $data->url_o ?? $data->original ?? null;

        return $urls;

    }
    
    
    /**
     * Get the cached data for a photo id
     * @param   int     $id
     * @return  object|null
     */
    public function getCachedPhotoData(int $id): ?object {
        return Craft::$app->cache->get("flickr-gallery-photo-data:$id") ?: null;
    }
    
    
    
    //=================================================//
    //================ Private Methods ================//
    //=================================================//
    
    /**
     * @param   string  $flickrMethod   api method to call (exclude the 'flickr.' at betinning)
     * @param   array   $params         any additional params to call
     */
    private function fetch(string $flickrMethod, array $params = []): ?object {
        $settings = Plugin::$plugin->getSettings();
        $oauthToken = (new TokensService())->getToken();
        if (!$oauthToken) return null;

        // Parameters
        $method = 'GET';
        $oauthParams = [
            'oauth_consumer_key'     => $settings->flickrApiKey,
            'oauth_nonce'            => bin2hex(random_bytes(16)),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp'        => time(),
            'oauth_token'            => $oauthToken->token,
            'oauth_version'          => '1.0',
        ];
        $apiParams = array_merge([
            'method'         => "flickr.$flickrMethod",
            'format'         => 'json',
            'nojsoncallback' => 1,
            'per_page'       => 20,
        ], $params);

        // Merge parameters
        $params = array_merge($oauthParams, $apiParams);

        // Generate the OAuth signature
        $oauthParams['oauth_signature'] = $this->generateOAuthSignature($method, self::BASE_URL, $params, $settings->flickrApiSecret, $oauthToken->secret);

        // Build the query string
        $query = http_build_query(array_merge($apiParams, $oauthParams));

        // skip oauth params, but still use flickr username and api key to distinguish between accounts that may switch
        $cacheQuery = http_build_query(array_merge($apiParams, ['api_key' => $settings->flickrApiKey, 'flickr_username' => $settings->flickrUsername]));

        // check our fetch cache
        $data = Craft::$app->cache->get("flickr-fetch:$cacheQuery");

        if (!$data) {
            // Make the request
            $client = Craft::createGuzzleClient();
            $response = $client->request($method, self::BASE_URL . "?" . $query);

            // Parse the response
            $data = json_decode($response->getBody()->getContents());

            if ( 'ok' != ($data?->stat ?? false) ) {
                Plugin::error("Flickr API failed to fetch data for query " . self::BASE_URL . "?" . $query . " with message: " . ($data?->message ?? 'undefined'));
                return null;
            }

            // cache the response
            Craft::$app->cache->set("flickr-fetch:$cacheQuery", $data, 60 * 60 * 2);
        }

        
        return $data;

    }


    private function generateOAuthSignature(string $method, string $url, array $params, string $consumerSecret, string $tokenSecret): string
    {
        // Sort parameters alphabetically by key
        ksort($params);
    
        // Concatenate key-value pairs as a query string
        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    
        // Create the signature base string
        $baseString = strtoupper($method) . '&' . rawurlencode($url) . '&' . rawurlencode($query);
    
        // Create the signing key
        $signingKey = rawurlencode($consumerSecret) . '&' . rawurlencode($tokenSecret);
    
        // Generate HMAC-SHA1 signature
        return base64_encode(hash_hmac('sha1', $baseString, $signingKey, true));
    }


    /**
     * Populate a photo object with image urls, return a standard format
     * @param   object  $photo
     * @param   ?string $size   optional - sets the size of the default url property
     * @return  object
     */
    private function photoDataResource(object $photo, ?string $size = null): object {

        // set the default url to be the original image
        $photo->url = $this->getImageUrl($photo);
        $photo->sizes = $this->getAllPhotoSizes($photo);
        $photo->original = $photo->original ?? $photo->url_o ?? null;

        // cache data for easy future reference
        if ($photo->id) {
            $this->cachePhotoData($photo->id, $photo);
        }

        // now, override the default url with the supplied size
        if ($size) {
            $photo->url = $this->getImageUrl($photo, $size);
        }

        return $photo;
    }


    /**
     * Cache photo data for the photo id
     * @param   int     $id
     * @param   object  $data
     */
    private function cachePhotoData(int $id, object $data) {

        if (!empty($data->title?->_content)) {
            $data->title = $data->title?->_content;
        }

        $data = (object)[
            'id' => $data->id ?? null,
            'url' => $data->sizes['b'] ?? $data->url ?? null,
            'original' => $data->original ?? $data->sizes['original'] ?? null,
            'title' => $data->title ?? null,
            'sizes' => $data->sizes ?? null,
        ];

        // cache for 2 hours
        Craft::$app->cache->set("flickr-gallery-photo-data:$id", $data, 60 * 60 * 2);
    }

    /**
     * Merge an array or comma separated list of "extras" to send as flickr api params. Returns a comma separated string
     * @param   mixed   $options
     * @return  string
     */
    private function getExtrasParam(mixed $options): string {

        // default
        $extras = ['url_o'];

        // make sure options is a valid format
        if ($options && (is_string($options) || is_array($options))) {

            // convert to array
            if (is_string($options)) {
                $options = array_map('trim', explode(',', $options));
            }

            // merge and get unique
            $extras = array_merge($extras, $options);
            $extras = array_values(array_unique($extras));
        }

        $result = join(', ', $extras);

        return $result;
    }

}
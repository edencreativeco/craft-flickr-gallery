<?php

namespace edencreative\craftflickrgallery\models;

use Craft;
use craft\base\Model;

/**
 * craft-flickr-gallery settings
 *
 * @author  Eden Creative <developers@edencreative.co>
 * @package Flickr Gallery
 * @since   1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================


    /**
     * @var string The public-facing name of the plugin
     */
    public string $pluginName = "Flickr Gallery";



    // Flickr Integration

    /**
     * @var string  username for Flickr
     */
    public string $flickrUsername = '';

    /**
     * @var string  API key for Flickr
     */
    public string $flickrApiKey = '';

    /**
     * @var string  API secret for Flickr
     */
    public string $flickrApiSecret = '';

    /**
     * @var string  Custom Callback Url
     */
    public string $callbackUrl = '';



    public function defineRules(): array
    {
        return [
            ['pluginName', 'string'],
            ['pluginName', 'default', 'value' => 'Flickr Gallery'],
            ['flickrUsername', 'string'],
            ['flickrUsername', 'default', 'value' => ''],
            ['flickrApiKey', 'string'],
            ['flickrApiKey', 'default', 'value' => ''],
            ['flickrApiSecret', 'string'],
            ['flickrApiSecret', 'default', 'value' => ''],
            ['callbackUrl', 'string'],
            ['callbackUrl', 'default', 'value' => ''],
        ];
    }
}

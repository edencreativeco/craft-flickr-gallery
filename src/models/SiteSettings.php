<?php

namespace edencreative\craftflickrgallery\models;

use Craft;
use craft\base\Model;
use craft\helpers\Json;
use edencreative\craftflickrgallery\models\SiteSettingsData;
use edencreative\craftflickrgallery\Plugin;

/**
 * Site Settings settings object, containing values that can be customized per environment
 *
 * @author  Eden Creative <developers@edencreative.co>
 * @package Flickr Gallery
 * @since   1.0.0
 * 
 */
class SiteSettings extends Model
{

    // Properties
    // =========================================================================

    /**
     * @var int
     */
    public $siteId;

    /**
     * @var SiteSettingsData
     */
    public $settingsData;

    /**
     * @var DateTime|null
     */
    public $dateCreated;

    /**
     * @var DateTime|null
     */
    public $dateUpdated;


    // Static Protected Methods
    // =========================================================================

    /**
     * Remove any properties that don't exist in the model
     *
     * @param string $class
     * @param array  $config
     */
    protected static function cleanProperties(string $class, array &$config)
    {
        foreach ($config as $propName => $propValue) {
            if (!property_exists($class, $propName)) {
                unset($config[$propName]);
            }
        }
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        if (isset($config['settingsData']) && is_string($config['settingsData'])) {
            $config['settingsData'] = Json::decode($config['settingsData']);
        }

        parent::__construct($config);
    }


    /**
     * Create a new site settings model
     *
     * @param array $config
     *
     * @return null|SiteSettings
     */
    public static function create(?array $config = null)
    {
        if (is_null($config)) {
            $config = self::getDefaultConfig();
        }

        // get settingsData and decode
        $data = $config['settingsData'] ?? [];
        if (is_string($data)) {
            try {
                $data = Json::decode($data);
            } catch (\Error $e) {
                Plugin::error('error decoding settings data');
                $data = [];
            }
        }

        $settingsData = new SiteSettingsData($data);
        $config['settingsData'] = $settingsData;

        self::cleanProperties(__CLASS__, $config);
        $model = new SiteSettings($config);

        return $model;
    }

    public function defineRules(): array
    {
        return [
            ['siteId', 'number', 'min' => 1],
        ];
    }


    // Private Methods
    // =========================================================================

    private static function getDefaultConfig(): array
    {
        return [
            'settingsData' => [
                'flickrAssetImportPathSource' => '',
                'flickrAssetImportPathSubpath' => '',
                'albumNameAsSubfolder' => 0,
            ],
            'siteId' => Craft::$app->getSites()->getCurrentSite()->id ?? 1
        ];
    }
}

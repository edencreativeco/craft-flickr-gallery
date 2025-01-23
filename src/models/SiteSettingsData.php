<?php

namespace edencreative\craftflickrgallery\models;

use Craft;
use craft\base\Model;
use craft\helpers\Json;

/**
 * Site Settings Data object, containing properties that will be json-encoded into the database
 * 
 * @author    edencreative
 * @package   Craft Flickr Gallery
 * @since     1.0.0
 * 
 */
class SiteSettingsData extends Model
{

    // Public Properties
    // =========================================================================

    /**
     * @var ?string $flickrAssetImportPathSource
     */
    public $flickrAssetImportPathSource;

    /**
     * @var ?string $flickrAssetImportPathSubpath
     */
    public $flickrAssetImportPathSubpath;

    /**
     * @var ?string $albumNameAsSubfolder
     */
    public $albumNameAsSubfolder;

    public function __construct($config = [])
    {
        if (!$config) $config = [];

        foreach ($config as $propName => $propValue) {
            if (!property_exists(self::class, $propName)) {
                unset($config[$propName]);
            }
        }

        parent::__construct($config);
    }

    // /**
    //  * @inheritdoc
    //  */
    // public function setAttributes($values, $safeOnly = true): void
    // {
    //     foreach ($values as $propName => $propValue) {
    //         if (!property_exists(self::class, $propName)) {
    //             unset($values[$propName]);
    //         }
    //     }
    //     parent::setAttributes($values);
    // }

    /**
     * @inheritdoc
     */
    public function defineRules(): array
    {
        // NOTE: any attributes not appearing in the rules list will not be assigned by the "setAttributes" method!

        return [
            [['flickrAssetImportPathSource', 'flickrAssetImportPathSubpath'], 'string'],
            ['flickrAssetImportPathSubpath', 'default', 'value' => ''],
            [['albumNameAsSubfolder'], 'boolean', 'trueValue' => 1, 'falseValue' => 0],
            [['albumNameAsSubfolder'], 'default', 'value' => 0]
        ];
    }
}

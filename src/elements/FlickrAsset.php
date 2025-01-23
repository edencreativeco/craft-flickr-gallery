<?php
    
namespace edencreative\craftflickrgallery\elements;

use craft\elements\Asset;
use edencreative\craftflickrgallery\elements\db\FlickrAssetQuery;
use edencreative\craftflickrgallery\services\FlickrService;

/**
 * @property-read   $flickrImportSizeFormatted
 */
class FlickrAsset extends Asset {

    /**
     * @var int photo_id
     */
    public $flickr_photo_id;

    /**
     * @var ?string album
     */
    public $flickr_album;

    /**
     * @var ?int album_id
     */
    public $flickr_album_id;

    /**
     * @var ?string import_size
     */
    public $flickr_import_size;


    /**
     * @inheritdoc
     * @return FlickrAssetQuery The newly created [[AssetQuery]] instance.
     */
    public static function find(): FlickrAssetQuery
    {
        return new FlickrAssetQuery(static::class);
    }


    public function getFlickrImportSizeFormatted(): string {

        $name = $this->flickr_import_size ?? "";

        if ( $this->flickr_import_size && isset(FlickrService::PHOTO_SIZES[$this->flickr_import_size]) ) {
            $name = FlickrService::PHOTO_SIZES[$this->flickr_import_size] . "px";
        }

        return ucfirst($name);
    }

}
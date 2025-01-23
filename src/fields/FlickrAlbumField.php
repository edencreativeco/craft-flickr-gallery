<?php
    
namespace edencreative\craftflickrgallery\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Json;
use craft\web\assets\cp\CpAsset;
use edencreative\craftflickrgallery\assets\AlbumFieldAssetBundle;

class FlickrAlbumField extends Field {

    public function getInputHtml(mixed $value, ?ElementInterface $element = null): string
    {

        $view = Craft::$app->getView();
        $view->registerAssetBundle(AlbumFieldAssetBundle::class);
        
        return $view->renderTemplate('craft-flickr-gallery/_fieldtypes/flickralbum', [
            'name' => $this->handle,
            'value' => $value
        ]);

    }

    // Serialize the field value before saving to the database
    public function serializeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        return $value;
        // return Json::decode($value);
    }

    // Unserialize field value when retrieving from the database
    public function normalizeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        return $value;
    }

    // Define the database column type for the field
    public static function valueType(): string
    {
        return 'int';        
    }

    public static function displayName(): string
    {
        return 'Flickr Album';
    }

}
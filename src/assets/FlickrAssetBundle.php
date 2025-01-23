<?php
namespace edencreative\craftflickrgallery\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class FlickrAssetBundle extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@craft-flickr-plugin/resources';

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = ['js/flickr-cp.js'];
        $this->css = ['css/flickr-cp.css'];


        // Include Craft's Control Panel assets
        $this->depends = [
            CpAsset::class, // Ensures jQuery, Garnish, and other Craft CP dependencies are loaded
        ];

        parent::init();
    }
}
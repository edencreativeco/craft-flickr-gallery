<?php 

namespace edencreative\craftflickrgallery\base;

use Craft;
use edencreative\craftflickrgallery\Plugin;

/**
 * @since 1.1.1
 */
trait PluginTrait {

    // Properties
    // =========================================================================

    /**
     * @var Plugin
     */
    public static Plugin $plugin;


    // Static Methods
    // =========================================================================

    /**
     * @param   string  $message
     */
    public static function info(string $message): void {
        Craft::info($message, 'flickr-gallery');
    }

    /**
     * @param   string  $message
     */
    public static function error(string $message): void {
        Craft::error("[error] $message", 'flickr-gallery');
    }

    
}

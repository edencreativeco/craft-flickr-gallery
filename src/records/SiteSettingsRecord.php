<?php

/**
 * Site Settings for craft-flickr-gallery plugin by EdenCreative
 */

namespace edencreative\craftflickrgallery\records;

use craft\db\ActiveRecord;
use edencreative\craftflickrgallery\db\Table;

/**
 * @author  Eden Creative <developers@edencreative.co>
 * @package Flickr Gallery
 * @since   1.0.0
 * 
 * @property int $id
 * @property string $uid
 * @property int $siteId
 * @property string $settingsData
 * @property DateTime $dateCreated
 * @property DateTime $dateUpdated
 */
class SiteSettingsRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return Table::SITE_SETTINGS;
    }
}

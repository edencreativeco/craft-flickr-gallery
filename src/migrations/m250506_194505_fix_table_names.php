<?php

namespace edencreative\craftflickrgallery\migrations;

use Craft;
use craft\db\Migration;

/**
 * m250506_194505_fix_table_names migration.
 */
class m250506_194505_fix_table_names extends Migration
{

    // Table Names
    // =========================================================================

    const TABLE_SITE_SETTINGS = '{{%flickr-gallery_site-settings}}';
    const TABLE_FLICKR_ASSETS ='{{%flickr-gallery_assets}}';

    const NEW_TABLE_SITE_SETTINGS = '{{%flickr_gallery_site_settings}}';
    const NEW_TABLE_FLICKR_ASSETS ='{{%flickr_gallery_assets}}';


    // Public Methods
    // =========================================================================


    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Place migration code here...
        $this->renameTable(self::TABLE_FLICKR_ASSETS, self::NEW_TABLE_FLICKR_ASSETS);
        $this->renameTable(self::TABLE_SITE_SETTINGS, self::NEW_TABLE_SITE_SETTINGS);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m250506_194505_fix_table_names cannot be reverted.\n";
        return false;
    }
}

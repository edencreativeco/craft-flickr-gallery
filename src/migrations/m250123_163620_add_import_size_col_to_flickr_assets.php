<?php

namespace edencreative\craftflickrgallery\migrations;

use Craft;
use craft\db\Migration;

/**
 * m250123_163620_add_import_size_col_to_flickr_assets migration.
 */
class m250123_163620_add_import_size_col_to_flickr_assets extends Migration
{
    const TABLE_FLICKR_ASSETS='{{%flickr-gallery_assets}}';

    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Place migration code here...
        $this->addColumn(self::TABLE_FLICKR_ASSETS, 'import_size', $this->string()->after('album_id')->defaultValue('original'));

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m250123_163620_add_import_size_col_to_flickr_assets cannot be reverted.\n";
        return false;
    }
}

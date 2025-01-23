<?php

namespace edencreative\craftflickrgallery\migrations;

use Craft;
use craft\db\Migration;

/**
 * m250115_152248_add_album_import_column migration.
 */
class m250115_152248_add_album_import_column extends Migration
{
    const TABLE_FLICKR_ASSETS='{{%flickr-gallery_assets}}';

    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Place migration code here...

        $this->addColumn(self::TABLE_FLICKR_ASSETS, 'album', $this->string()->after('photo_id'));
        $this->addColumn(self::TABLE_FLICKR_ASSETS, 'album_id', $this->bigInteger()->after('album'));
        $this->createIndex(null, self::TABLE_FLICKR_ASSETS, ['album']);
        $this->createIndex(null, self::TABLE_FLICKR_ASSETS, ['album_id']);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m250115_152248_add_album_import_column cannot be reverted.\n";
        return false;
    }
}

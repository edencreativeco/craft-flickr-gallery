<?php

namespace edencreative\craftflickrgallery\migrations;

use Craft;
use craft\db\Migration;

/**
 * m250114_192130_add_flickr_asset_id_table migration.
 */
class m250114_192130_add_flickr_asset_id_table extends Migration
{
    const TABLE_FLICKR_ASSETS='{{%flickr-gallery_assets}}';

    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Place migration code here...

        if ($this->createTables()) {
            $this->addForeignKeys();
            $this->createIndexes();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m250114_192130_add_flickr_asset_id_table cannot be reverted.\n";
        return false;
    }


    /**
     * @return bool
     */
    protected function createTables(): bool {

        if ($this->db->tableExists(self::TABLE_FLICKR_ASSETS)) return false;
        
        // create the settings table
        $this->createTable(self::TABLE_FLICKR_ASSETS, [
            'id' => $this->integer()->notNull(),
            'photo_id' => $this->bigInteger()->unsigned()->notNull(),
            'PRIMARY KEY(id)',
        ]);

        echo "flickr assets table created.\n";

        return true;        
    }


    protected function addForeignKeys() {

        $elementsTable = '{{%elements}}';

        $this->addForeignKey(null, self::TABLE_FLICKR_ASSETS, ['id'], $elementsTable, ['id'], 'CASCADE', null);
    }

    protected function createIndexes() {

        $this->createIndex(null, self::TABLE_FLICKR_ASSETS, ['photo_id']);

    }
}

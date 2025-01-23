<?php

namespace edencreative\craftflickrgallery\migrations;

use Craft;
use craft\db\Migration;
use craft\helpers\Json;
use edencreative\craftflickrgallery\models\SiteSettingsData;
use edencreative\craftflickrgallery\records\SiteSettingsRecord;

/**
 * m250110_194345_flickr_gallery_site_settings migration.
 */
class m250110_194345_flickr_gallery_site_settings extends Migration
{

    // Table Names
    // =========================================================================
    const TABLE_SITE_SETTINGS = '{{%flickr-gallery_site-settings}}';

    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Place migration code here...

        if ($this->createTables()) {
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->removeTables();
        return false;
    }

    // Protected Methods
    // =========================================================================


    /**
     * @return bool
     */
    protected function createTables(): bool {

        if ($this->db->tableExists(self::TABLE_SITE_SETTINGS)) return false;
        
        // create the settings table
        $this->createTable(self::TABLE_SITE_SETTINGS, [
            'id int NOT NULL AUTO_INCREMENT',
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
            'siteId' => $this->integer()->notNull()->unique(),
            'settingsData' => $this->string(),
            'PRIMARY KEY(id)',
        ]);

        echo "site settings table created.\n";

        return true;        
    }


    protected function addForeignKeys() {

        $this->addForeignKey(
            $this->db->getForeignKeyName(self::TABLE_SITE_SETTINGS, 'siteId'),
            self::TABLE_SITE_SETTINGS,
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    protected function insertDefaultData() {

        $sites = Craft::$app->getSites()->getAllSites();

        foreach ($sites as $site) {
            $settings = Json::encode(
                new SiteSettingsData()
            );

            $settingsRecord = new SiteSettingsRecord([
                'siteId' => $site->id,
                'settingsData' => $settings,
            ]);
            $settingsRecord->save();
        }
    }

    protected function removeTables(): void
    {
        $this->dropTableIfExists(self::TABLE_SITE_SETTINGS);
    }
    
}

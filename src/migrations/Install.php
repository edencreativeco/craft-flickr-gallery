<?php

namespace edencreative\craftflickrgallery\migrations;

use Craft;
use craft\db\Migration;
use craft\helpers\Json;
use edencreative\craftflickrgallery\models\SiteSettingsData;
use edencreative\craftflickrgallery\records\SiteSettingsRecord;
/**
 * Install migration.
 */
class Install extends Migration
{

    // Table Names
    // =========================================================================
    const TABLE_FLICKR_TOKENS = '{{%flickr_tokens}}';
    const TABLE_SITE_SETTINGS = '{{%flickr_gallery_site_settings}}';
    const TABLE_FLICKR_ASSETS ='{{%flickr_gallery_assets}}';


    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();
        $this->insertDefaultData();

        return true;
    }
    

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->removeForeignKeys();
        $this->removeTables();
        
        return true;
    }


    // Protected Methods
    // =========================================================================

    protected function createTables(): void
    {

        if (!$this->db->tableExists(self::TABLE_FLICKR_TOKENS)) {

            // Create the Flickr Tokens table:
            $this->createTable(self::TABLE_FLICKR_TOKENS, [
                'id' => $this->primaryKey(),
                'username' => $this->string(),
                'token' => $this->string(),
                'secret' => $this->string(),                
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid()->notNull(),
            ]);

        }


        if (!$this->db->tableExists(self::TABLE_SITE_SETTINGS)) {
            
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
    
        }


        // create the flickr assets table
        if (!$this->db->tableExists(self::TABLE_FLICKR_ASSETS)) {

            $this->createTable(self::TABLE_FLICKR_ASSETS, [
                'id' => $this->integer()->notNull(),
                'photo_id' => $this->bigInteger()->unsigned()->notNull(),
                'album' => $this->string(),
                'album_id' => $this->bigInteger(),
                'import_size' => $this->string()->defaultValue('original'),
                'PRIMARY KEY(id)',
            ]);

            echo "flickr assets table created.\n";
        }

    }

    protected function createIndexes(): void {
        $this->createIndex(null, self::TABLE_FLICKR_ASSETS, ['photo_id']);
        $this->createIndex(null, self::TABLE_FLICKR_ASSETS, ['album']);
        $this->createIndex(null, self::TABLE_FLICKR_ASSETS, ['album_id']);
    }

    protected function addForeignKeys(): void {

        $elementsTable = '{{%elements}}';

        // site settings -> site id
        $this->addForeignKey( null, self::TABLE_SITE_SETTINGS, 'siteId', '{{%sites}}', 'id', 'CASCADE', 'CASCADE' );

        // flickr assets -> element id
        $this->addForeignKey( null, self::TABLE_FLICKR_ASSETS, ['id'], $elementsTable, ['id'], 'CASCADE', null );

    }

    protected function insertDefaultData(): void {


        $sites = Craft::$app->getSites()->getAllSites();

        foreach ($sites as $site) {
            $currentRecord = SiteSettingsRecord::find()->where(['siteId' => $site->id])->one();
            if ($currentRecord) continue;

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

    protected function removeForeignKeys(): void {
        $this->dropAllForeignKeysToTable(self::TABLE_SITE_SETTINGS);
    }

    protected function removeTables(): void
    {
        $this->dropTableIfExists(self::TABLE_FLICKR_TOKENS);
        $this->dropTableIfExists(self::TABLE_SITE_SETTINGS);
    }
}

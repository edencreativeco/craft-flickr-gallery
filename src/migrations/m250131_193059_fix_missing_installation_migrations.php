<?php

namespace edencreative\craftflickrgallery\migrations;

use Craft;
use craft\db\Migration;
use craft\helpers\Json;
use edencreative\craftflickrgallery\models\SiteSettingsData;
use edencreative\craftflickrgallery\records\SiteSettingsRecord;

/**
 * m250131_193059_fix_missing_installation_migrations migration.
 */
class m250131_193059_fix_missing_installation_migrations extends Migration
{
    // Table Names
    // =========================================================================
    const TABLE_FLICKR_TOKENS = '{{%flickr_tokens}}';
    const TABLE_SITE_SETTINGS = '{{%flickr-gallery_site-settings}}';
    const TABLE_FLICKR_ASSETS='{{%flickr-gallery_assets}}';


    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->createTables();
        $this->insertDefaultData();

        return true;
    }
    

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m250131_193059_fix_missing_installation_migrations cannot be reverted.\n";
        return false;
    }


    // Protected Methods
    // =========================================================================

    protected function createTables(): void
    {
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

            $this->addForeignKey( null, self::TABLE_SITE_SETTINGS, 'siteId', '{{%sites}}', 'id', 'CASCADE', 'CASCADE' );

            echo "foreign keys added to flickr site settings table.\n";
    
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

            $this->createIndex(null, self::TABLE_FLICKR_ASSETS, ['photo_id']);
            $this->createIndex(null, self::TABLE_FLICKR_ASSETS, ['album']);
            $this->createIndex(null, self::TABLE_FLICKR_ASSETS, ['album_id']);

            echo "flickr assets table indexed.\n";

            $this->addForeignKey( null, self::TABLE_FLICKR_ASSETS, ['id'], '{{%elements}}', ['id'], 'CASCADE', null );

            echo "foreign keys added to flickr assets table.\n";

        }

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
}

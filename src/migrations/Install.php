<?php

namespace edencreative\craftflickrgallery\migrations;

use Craft;
use craft\db\Migration;

/**
 * Install migration.
 */
class Install extends Migration
{

    // Table Names
    // =========================================================================
    const TABLE_FLICKR_TOKENS = '{{%flickr_tokens}}';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->createTables();

        return true;
    }
    

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->removeTables();
        
        return true;
    }


    // Protected Methods
    // =========================================================================

    protected function createTables(): void
    {
        $flickrTokensTable = self::TABLE_FLICKR_TOKENS;

        if ($this->db->tableExists($flickrTokensTable)) return;

        // Create the Flickr Tokens table:
        $this->createTable($flickrTokensTable, [
            'id' => $this->primaryKey(),
            'username' => $this->string(),
            'token' => $this->string(),
            'secret' => $this->string(),                
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()->notNull(),
        ]);


    }

    protected function removeTables(): void
    {
        $this->dropTableIfExists(self::TABLE_FLICKR_TOKENS);
    }
}

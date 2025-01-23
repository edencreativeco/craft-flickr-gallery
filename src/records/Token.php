<?php
    
namespace edencreative\craftflickrgallery\records;

use craft\db\ActiveRecord;
use edencreative\craftflickrgallery\db\Table;

/**
 * Token record
 * 
 * @property    int     $id
 * @property    string  $username
 * @property    string  $token
 * @property    string  $secret
 */
class Token extends ActiveRecord {

    // Public Methods
    // =========================================================================

    /**
     * Returns the name of the associated database table.
     *
     * @return string
     */
    public static function tableName(): string
    {
        return Table::FLICKR_TOKENS;
    }

}
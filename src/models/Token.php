<?php

namespace edencreative\craftflickrgallery\models;

use craft\base\Model;
use DateTime;

class Token extends Model {

    // Properties
    // =========================================================================

    /**
     * @var ?int ID
     */
    public $id;

    /**
     * @var ?string Username
     */
    public $username;


    /**
     * @var ?string Token
     */
    public $token;

    /**
     * @var ?string Secret
     */
    public $secret;

    /**
     * @var ?DateTime Date updated
     */
    public $dateUpdated;

    /**
     * @var ?DateTime Date created
     */
    public $dateCreated;

    /**
     * @var ?string Uid
     */
    public $uid;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['id'], 'number', 'integerOnly' => true],
        ];
    }
}
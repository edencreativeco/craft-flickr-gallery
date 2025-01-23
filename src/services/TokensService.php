<?php
    
namespace edencreative\craftflickrgallery\services;

use Craft;
use craft\base\Component;
use edencreative\craftflickrgallery\models\Token;
use edencreative\craftflickrgallery\Plugin;
use edencreative\craftflickrgallery\records\Token as TokenRecord;
use Exception;

class TokensService extends Component {

    /**
     * @return ?Token
     */
    public function getToken(?string $username = null): ?Token {

        if (!$username) {
            $settings = Plugin::$plugin->getSettings();
            $username = $settings->flickrUsername;
        }

        $token = TokenRecord::find()
            ->where([
                'username' => $username,
            ])
            ->one();

        if (!$token) return null;

        return new Token($token->toArray([
            'id',
            'username',
            'token',
            'secret',
        ]));
    }


    /**
     * Save Flickr oauth tokens
     * @param   Token   $token
     * @param   bool    $runValidation
     * @return  bool
     */
    public function saveToken(Token $token, bool $runValidation = true): bool {

        if ($runValidation && !$token->validate()) {
            Craft::info('Token not saved due to validation error.', __METHOD__);

            return false;
        }

        $isNew = !$token->id;

        if ($isNew) {
            $tokenRecord = new TokenRecord();
        } else {
            $tokenRecord = TokenRecord::findOne($token->id);

            if (!$tokenRecord) {
                throw new Exception("No token exists with the ID '$token->id'");
            }
        }

        $tokenRecord->token = $token->token;
        $tokenRecord->secret = $token->secret;
        $tokenRecord->username = $token->username;

        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            // Already ran validation
            $tokenRecord->save(false);

            // Save id to model, if it's a new token
            if ($isNew) {
                $token->id = $tokenRecord->id;
            }

            $transaction->commit();
        } catch (Exception $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        return true;
    }

    /**
     * Deletes a token.
     *
     * @param int $id
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteTokenById(int $id): bool
    {
        $tokenRecord = TokenRecord::findOne($id);

        if (!$tokenRecord instanceof TokenRecord) return true;

        $tokenRecord->delete();

        return true;
    }
    
}
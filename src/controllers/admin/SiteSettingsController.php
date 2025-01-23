<?php

namespace edencreative\craftflickrgallery\controllers\admin;

use Craft;
use craft\elements\Asset;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\web\Response;
use craft\web\Controller;
use edencreative\craftflickrgallery\models\SiteSettingsData;
use edencreative\craftflickrgallery\Plugin;
use edencreative\craftflickrgallery\records\SiteSettingsRecord;

class SiteSettingsController extends Controller
{
    // Properties
    // =========================================================================

    const SUB_NAV = 'site-settings';
    // protected array|bool|int $allowAnonymous = ['index'];


    // Public Methods
    // =========================================================================


    public function actionEdit(): Response
    {

        // Get settings from site ID
        $settingsData = Plugin::$plugin->siteSettings;
        
        // Set variables
        $variables = [
            'settings' => $settingsData,
            'selectedSubnavItem' => self::SUB_NAV,
            'formActions' => [],
        ];


        // Get available volume sources for asset upload path field
        $volumeSourceOptions = [];
        foreach (Asset::sources('settings') as $volume) {
            if (!isset($volume['heading'])) {
                $volumeSourceOptions[] = [
                    'label' => $volume['label'],
                    'value' => $volume['key'],
                ];
            }
        }
        $variables['volumeSourceOptions'] = $volumeSourceOptions;


        $variables['crumbs'] = [
            [
                'label' => Plugin::$plugin->settings->pluginName,
                'url' => UrlHelper::cpUrl('flickr-gallery'),
            ],
            [
                'label' => 'Site Settings',
                'url' => UrlHelper::cpUrl('edenhockey/site-settings'),
            ],
        ];

        return $this->renderTemplate("craft-flickr-gallery/site-settings", $variables);
    }

    public function actionSave(): ?Response
    {
        
        $params = $this->request->getBodyParams();


        // // Get settings record from site ID
        $siteId = $siteId ?? Craft::$app->getSites()->getCurrentSite()->id ?? 1;
        $siteSettingsRecord = SiteSettingsRecord::find()->where(['siteId' => $siteId])->one();

        if (!$siteSettingsRecord) {
            $siteSettingsRecord = new SiteSettingsRecord();
            $siteSettingsRecord->setAttribute( 'siteId', $siteId );
        }

        // update site settings data
        $currentSettingsData = new SiteSettingsData( $siteSettingsRecord->settingsData ? Json::decode($siteSettingsRecord->settingsData) : null );

        //safe mode - only sets attributes with validation rules on the SiteSettingsData model
        $currentSettingsData->setAttributes($params);


        if (!$currentSettingsData->validate()) {
            $message = 'Site Settings not saved due to validation error.';
            Plugin::error($message);
            Craft::info($message, __METHOD__);
            $this->setFailFlash($message);
            return null;
        }


        // // save updated data to record
        $siteSettingsRecord->settingsData = Json::encode($currentSettingsData);


        // try to save it
        if (!$siteSettingsRecord->save()) {
            $message = 'An error occurred - Site settings not saved';
            Plugin::error($message);
            Craft::info($message, __METHOD__);
            $this->setFailFlash($message);
            return false;
        }


        if ($this->request->acceptsJson) {
            return $this->asJson(['success' => true]);
        }


        $this->setSuccessFlash('Site Settings saved.');
        return $this->refresh();
    }
    

}

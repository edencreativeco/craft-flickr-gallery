<?php
    
namespace edencreative\craftflickrgallery\services;

use Craft;
use craft\base\Component;
use craft\db\Query;
use edencreative\craftflickrgallery\db\Table;
use edencreative\craftflickrgallery\models\SiteSettings;

class SiteSettingsService extends Component {

    /**
     * Get the global site settings for a site ID
     * @param   int $siteId
     * 
     * @return null|SiteSettings
     */
    public function getSiteSettings(?int $siteId = null): SiteSettings {
        $siteId = $siteId ?? Craft::$app->getSites()->getCurrentSite()->id ?? 1;
        $siteSettings = null;


        $siteSettingsArray = (new Query())
            ->from([Table::SITE_SETTINGS])
            ->where([
                'siteId' => $siteId,
            ])
            ->one();
        
        if (!empty($siteSettingsArray)) {
            $siteSettings = SiteSettings::create($siteSettingsArray);
        } else {
            // create model with default settings
            $siteSettings = SiteSettings::create([
                'siteId' => $siteId
            ]);
        }

        return $siteSettings;

    }
}
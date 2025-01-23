<?php

namespace edencreative\craftflickrgallery;

use Craft;
use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\elements\Asset;
use craft\events\DefineAttributeKeywordsEvent;
use craft\events\RegisterElementSearchableAttributesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\log\MonologTarget;
use craft\services\Fields;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\web\View;
use edencreative\craftflickrgallery\models\SiteSettingsData;
use edencreative\craftflickrgallery\assets\FlickrAssetBundle;
use edencreative\craftflickrgallery\base\PluginTrait;
use edencreative\craftflickrgallery\elements\db\FlickrAssetQuery;
use edencreative\craftflickrgallery\elements\FlickrAsset;
use edencreative\craftflickrgallery\fields\FlickrAlbumField;
use edencreative\craftflickrgallery\models\Settings;
use edencreative\craftflickrgallery\services\AuthService;
use edencreative\craftflickrgallery\services\SiteSettingsService;
use edencreative\craftflickrgallery\services\TokensService;
use edencreative\craftflickrgallery\services\TwigService;
use Monolog\Formatter\LineFormatter;
use Psr\Log\LogLevel;
use yii\base\Event;

/**
 * Flickr Gallery plugin
 * 
 * @author Eden Creative <developers@edencreative.co>
 * @copyright Eden Creative
 * @license MIT
 *
 * @method static Plugin getInstance()
 * @method Settings getSettings()
 * @property-read   bool    hasFlickrCredentials
 * @property-read SiteSettingsData $siteSettings
 */
class Plugin extends BasePlugin
{
    use PluginTrait;

    public string $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public bool $hasCpSettings = true;

    /**
     * @var bool
     */
    public bool $hasCpSection = true;

    /**
     * @var ?SiteSettingsData
     */
    private ?SiteSettingsData $_siteSettings = null;


    public static function config(): array
    {
        return [
            'components' => [
                // Define component configs here...
            ],
        ];
    }

    public function init(): void
    {
        parent::init();

        self::$plugin = $this;

        // Define a custom alias using the module ID
        Craft::setAlias('@craft-flickr-plugin', __DIR__);


        $request = Craft::$app->getRequest();

        // Add JS and CSS resources to admin panel
        if (
            $this->isInstalled
            && $request->isCpRequest
        ) {
            $this->registerAssetBundles();
        }

        // Set custom logfile
        $this->_setCustomLogger();

        // Load Services
        $this->setComponents([
            'flickr' => TwigService::class,
        ]);


        $this->_registerTemplateRoots();
        $this->_registerCpRoutes();
        $this->_registerApiRoutes();

        $this->_registerCustomFieldTypes();

        $this->_extendTwig();



        // make sure to add search keywords when saving assets
        Event::on(
            Asset::class,
            Asset::EVENT_REGISTER_SEARCHABLE_ATTRIBUTES,
            function (RegisterElementSearchableAttributesEvent $event) {
                error_log('asset searchable attributes');
                $event->attributes[] = 'flickr_photo_id';
                $event->attributes[] = 'flickr_album';
                $event->attributes[] = 'flickr_album_id';
            }
        );

        Event::on(
            Asset::class,
            Asset::EVENT_DEFINE_KEYWORDS,
            function (DefineAttributeKeywordsEvent $event) {

                if (!in_array($event->attribute, ['flickr_photo_id', 'flickr_album', 'flickr_album_id'])) return;

                $asset = $event->sender;

                $flickrAsset = FlickrAsset::find()
                    ->id($asset->id)
                    ->select(['flickr_photo_id', 'flickr_album', 'flickr_album_id'])
                    ->one();

                if ($flickrAsset) {
                    $event->keywords = $flickrAsset->{$event->attribute} ?? "";
                } else {
                    $event->keywords = '';
                }

                $event->handled = true;
            }
        );


        // Any code that creates an element query or loads Twig should be deferred until
        // after Craft is fully initialized, to avoid conflicts with other plugins/modules
        Craft::$app->onInit(function () {
            // ...
        });
    }

    public function getHasFlickrCredentials(): bool
    {
        $ts = new TokensService();
        $token = $ts->getToken();

        return !!$token;
    }

    /**
     * @inheritdoc
     */
    public function getCpNavItem(): ?array
    {
        /** @var User $currentUser */
        $currentUser = Craft::$app->getUser()->getIdentity();

        if (!$currentUser->can('flickr-gallery')) return null;

        $subNav = [
            'import' => [
                'label' => 'Import',
                'url' => 'flickr-gallery/import'
            ],
            'index' => [
                'label' => 'Assets',
                'url' => 'flickr-gallery/assets'
            ],
        ];


        if ($currentUser->can('flickr-gallery:site-settings')) {
            $subNav['site-settings'] = [
                'url' => 'flickr-gallery/site-settings',
                'label' => 'Site Settings'
            ];
        }

        if (Craft::$app->getConfig()->general->allowAdminChanges) {
            $subNav = array_merge($subNav, [
                'system-settings' => [
                    'url' => 'settings/plugins/craft-flickr-gallery',
                    'label' => 'System Settings'
                ]
            ]);
        }

        return [
            'label' => 'Flickr Gallery',
            'url' => 'flickr-gallery',
            'icon' => '@craft-flickr-plugin/assets/icons/flickr-icon.svg',
            'subnav' => $subNav
        ];
    }


    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        $authService = new AuthService();
        return Craft::$app->view->renderTemplate('craft-flickr-gallery/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
            'callbackUrl' => $authService->getCallbackUrl()
        ]);
    }


    /**
     * @inheritdoc
     */
    public function getSiteSettings(): ?SiteSettingsData
    {
        if (!$this->_siteSettings) {

            $siteSettingsService = new SiteSettingsService();
            $settings = $siteSettingsService->getSiteSettings();
            $this->_siteSettings = $settings?->settingsData ?? new SiteSettingsData();
        }

        return $this->_siteSettings;
    }


    private function _registerCpRoutes(): void
    {


        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {

                $event->rules = array_merge($event->rules, [
                    'flickr-gallery' => 'craft-flickr-gallery/admin/flickr/import',
                    'flickr-gallery/import' => 'craft-flickr-gallery/admin/flickr/import',
                    'flickr-gallery/assets' => 'craft-flickr-gallery/admin/flickr/assets',
                    'flickr-gallery/site-settings' => 'craft-flickr-gallery/admin/site-settings/edit',
                ]);
            }
        );
    }

    private function _registerApiRoutes(): void
    {

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {

                $event->rules = array_merge($event->rules, [
                    // cp api
                    'GET api/cp-flickr-gallery/photos' => 'craft-flickr-gallery/flickr/photos',
                    'GET api/cp-flickr-gallery/albums' => 'craft-flickr-gallery/flickr/albums',
                    'GET api/cp-flickr-gallery/albums/<albumId:\d+>' => 'craft-flickr-gallery/flickr/single-album',
                    'POST api/cp-flickr-gallery/albums/<albumId:\d+>/import' => 'craft-flickr-gallery/flickr/import-album',
                    'POST api/cp-flickr-gallery/photos/import' => 'craft-flickr-gallery/flickr/import-photos',

                    // public api
                    'GET api/flickr-gallery/albums/<albumId:\d+>' => 'craft-flickr-gallery/public-flickr/single-album',
                ]);
            }
        );
    }

    private function _registerCustomFieldTypes(): void
    {
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function ($event) {
                $event->types[] = FlickrAlbumField::class;
            }
        );
    }


    private function _extendTwig(): void
    {

        // extend twig with cart service
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $e) {
                /** @var CraftVariable $variable */
                $variable = $e->sender;

                // Attach a service:
                $variable->set('flickr', $this->flickr::class);
            }
        );
    }


    /**
     * 
     */
    private function _registerTemplateRoots(): void
    {

        // Register template roots
        Event::on(
            View::class,
            View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
            function (RegisterTemplateRootsEvent $event) {
                $event->roots['craft-flickr-gallery'] = __DIR__ . '/templates';
            }
        );

        // Register template roots
        Event::on(
            View::class,
            View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS,
            function (RegisterTemplateRootsEvent $event) {
                $event->roots['craft-flickr-gallery'] = __DIR__ . '/templates';
            }
        );
    }



    private function _setCustomLogger(): void
    {

        // Register a custom log target, keeping the format as simple as possible.
        Craft::getLogger()->dispatcher->targets[] = new MonologTarget([
            'name' => 'flickr-gallery',
            'categories' => ['flickr-gallery'],
            'level' => LogLevel::INFO,
            'logContext' => false,
            'allowLineBreaks' => false,
            'formatter' => new LineFormatter(
                format: "%datetime% %message%\n",
                dateFormat: 'Y-m-d H:i:s',
            ),
        ]);
    }


    // Protected Methods
    // =========================================================================

    protected function registerAssetBundles()
    {
        // Include CSS/JS on control panel
        $view = Craft::$app->getView();
        $view->registerAssetBundle(FlickrAssetBundle::class);
    }
}

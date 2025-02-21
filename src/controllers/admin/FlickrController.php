<?php

namespace edencreative\craftflickrgallery\controllers\admin;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use edencreative\craftflickrgallery\elements\FlickrAsset;
use edencreative\craftflickrgallery\Plugin;

/**
 * Flickr Admin Controller
 */
class FlickrController extends Controller {


    // Properties
    // =========================================================================


    // Public Methods
    // =========================================================================

    public function actionAssets() {

        $perpage = intval(Craft::$app->getRequest()->getQueryParam('perpage') ?? 50);
        $page = intval(Craft::$app->getRequest()->getQueryParam('page') ?? 1);
        if ($page < 0) $page = 1;

        $search = Craft::$app->getRequest()->getQueryParam('q', null);

        $offset = ($page - 1) * $perpage;

        $query = FlickrAsset::find()
            ->limit($perpage)
            ->offset($offset)
            ->orderBy('dateCreated DESC');
        
        if ($search) $query->search($search);

        $total = (clone $query)->count();

        $assets = $query->all();

        // pagination
        $pagination = [
            'page' => $page,
            'perpage' => $perpage,
            'pages' => ceil($total / $perpage),
            'total' => $total,
            'start' => count($assets) ? $offset + 1 : 0,
            'end' => count($assets) ? $offset + count($assets) : 0,
        ];

        return $this->renderTemplate('craft-flickr-gallery/cp/_index.twig', [
            'selectedSubnavItem' => 'index',
            'flickrAssets' => $assets,
            'pagination' => $pagination,
            'searchQuery' => $search,
            'title' => 'Flickr Assets',
            'crumbs' => [
                [
                    'label' => Plugin::$plugin->settings->pluginName,
                    'url' => UrlHelper::cpUrl('flickr-gallery'),
                ],
                [
                    'label' => 'Assets',
                    'url' => UrlHelper::cpUrl('flickr-gallery/assets'),
                ],
            ]
        ]);
    }

    public function actionImport() {

        return $this->renderTemplate('craft-flickr-gallery/cp/_import.twig', [
            'selectedSubnavItem' => 'import',
            'title' => 'Flickr Asset Import',
            'crumbs' => [
                [
                    'label' => Plugin::$plugin->settings->pluginName,
                    'url' => UrlHelper::cpUrl('flickr-gallery'),
                ],
                [
                    'label' => 'Import',
                    'url' => UrlHelper::cpUrl('flickr-gallery/import'),
                ],
            ]            
        ]);
    }

}
<?php

namespace edencreative\craftflickrgallery\controllers\admin;

use Craft;
use craft\web\Controller;
use edencreative\craftflickrgallery\Plugin;
use craft\web\Response;
use edencreative\craftflickrgallery\elements\FlickrAsset;

/**
 * Flickr Admin Controller
 */
class FlickrController extends Controller {


    // Properties
    // =========================================================================
    // protected array|bool|int $allowAnonymous = ['create'];


    // Public Methods
    // =========================================================================

    public function actionAssets() {

        $perpage = intval(Craft::$app->getRequest()->getQueryParam('perpage') ?? 100);
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
        ]);
    }

    public function actionImport() {

        return $this->renderTemplate('craft-flickr-gallery/cp/_import.twig', [
            'selectedSubnavItem' => 'import'
        ]);
    }

}
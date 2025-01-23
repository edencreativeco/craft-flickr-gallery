<?php

namespace edencreative\craftflickrgallery\controllers;

use Craft;
use craft\web\Controller;
use craft\web\Response;
use edencreative\craftflickrgallery\services\FlickrService;

/**
 * Public Flickr Controller
 */
class PublicFlickrController extends Controller {

    protected array|bool|int $allowAnonymous = true;


    // public function actionAlbums(): Response {

    //     $fs = new FlickrService();
    //     $data = $fs->getPhotosets();

    //     return $this->asJson($data->photosets);
    // }


    public function actionSingleAlbum(int $albumId): Response {
        $fs = new FlickrService();

        // get query params
        $params = Craft::$app->getRequest()->getQueryParams();

        $options = [
            'size' => $params['size'] ?? 'b'
        ];
        if (isset($params['page'])) $options['page'] = $params['page'];
        if (isset($params['perpage'])) $options['perpage'] = $params['perpage'];

        $data = $fs->getPhotoset($albumId, $options);

        return $this->asJson($data->photoset);
    }

}
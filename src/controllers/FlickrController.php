<?php

namespace edencreative\craftflickrgallery\controllers;

use Craft;
use craft\helpers\Queue;
use craft\web\Controller;
use craft\web\Response;
use edencreative\craftflickrgallery\Plugin;
use edencreative\craftflickrgallery\queue\jobs\ImportFlickrAlbum;
use edencreative\craftflickrgallery\queue\jobs\ImportFlickrPhotos;
use edencreative\craftflickrgallery\services\AssetsService;
use edencreative\craftflickrgallery\services\FlickrService;

/**
 * Flickr Controller
 */
class FlickrController extends Controller {

    protected array|bool|int $allowAnonymous = false;


    public function actionPhotos(): Response {

        $size = $this->request->getQueryParam('size', 'm');
        $page = $this->request->getQueryParam('page');
        $perpage = $this->request->getQueryParam('perpage');
        $checkImported = (bool)$this->request->getQueryParam('check_imported', false);

        $fs = new FlickrService();
        $data = $fs->getPhotos([
            'size' => $size,
            'page' => $page,
            'perpage' => $perpage,
        ]);


        if ($checkImported && is_array($data?->photos?->photo ?? null)) {
            $data->photos->photo = $fs->identifyImportedPhotos($data->photos->photo);
        }

        return $this->asJson($data?->photos);
    }

    public function actionAlbums(): Response {

        $page = $this->request->getQueryParam('page');
        $perpage = $this->request->getQueryParam('perpage');

        $fs = new FlickrService();
        $data = $fs->getPhotosets([
            'page' => $page,
            'perpage' => $perpage,
        ]);

        return $this->asJson($data?->photosets);
    }


    public function actionSingleAlbum(int $albumId): Response {

        $size = $this->request->getQueryParam('size', 'm');
        $page = $this->request->getQueryParam('page');
        $perpage = $this->request->getQueryParam('perpage');
        $checkImported = (bool)$this->request->getQueryParam('check_imported', false);

        $fs = new FlickrService();

        $data = $fs->getPhotoset($albumId, [
            'size' => $size,
            'page' => $page,
            'perpage' => $perpage,
        ]);


        if ($checkImported && is_array($data->photoset?->photo ?? null)) {
            $data->photoset->photo = $fs->identifyImportedPhotos($data->photoset->photo);
        }

        return $this->asJson($data->photoset);
    }

    public function actionImportAlbum(int $albumId): Response {

        $importSize = $this->request->getBodyParam('import_size', 'original');

        $job = new ImportFlickrAlbum([
            'albumId' => $albumId,
            'importSize' => $importSize,
        ]);

        Queue::push($job);

        $message = 'Flickr album import job added to the queue';

        return $this->asJson([
            'success' => true,
            // 'photos' => array_map( fn($photo) => $photo->url, $data->photoset->photo),
            'message' => $message
        ]);
    }


    public function actionImportPhotos(): Response {

        $fs = new FlickrService();

        $photoIds = $this->request->getBodyParam('ids', []);
        $albumId = $this->request->getBodyParam('albumId', null);
        $albumName = $albumId ? ($fs->getPhotoset($albumId))?->photoset?->title : null;
        $importSize = $this->request->getBodyParam('import_size', 'original');

        if (!$photoIds) return $this->asJson([
            'success' => false,
            'errors' => ['missing required param "ids"'],
            'new_assets' => [],
            'message' => "Error - no images provided for import"
        ]);


        $job = new ImportFlickrPhotos([
            'photoIds' => $photoIds,
            'albumId' => $albumId,
            'albumName' => $albumName,
            'importSize' => $importSize,
        ]);

        if (count($photoIds) > 5) {
            // Add to Queue
            Queue::push($job);
            $message = "Photo import job added to the queue";

            return $this->asJson([
                'success' => true,
                'errors' => null,
                'new_assets' => null,
                'message' => $message
            ]);

        }


        // Process Immediately
        $queue = Craft::$app->getQueue();
        $job->execute($queue);
        $importedAssetIds = $job->importedIds;
        $importedFlickrIds = $job->importedFlickrIds;
        $importErrors = $job->importErrors;

        // Set success status and return message
        if (count($importErrors)) {
            $success = false;
            $message = count($importedAssetIds) ?
                "Some images could not be imported. See logs for details" :
                "The selected image" . (count($photoIds) == 1 ? "" : "s") . " could not be imported. See logs for details.";
        } else {
            $success = true;
            $message = 'Successfully imported all images!';
    
        }

        return $this->asJson([
            'success' => $success,
            'errors' => $importErrors ?: null,
            'new_assets' => $importedAssetIds,
            'imported_flickr_photos' => $importedFlickrIds,
            'message' => $message
        ]);
    }

}
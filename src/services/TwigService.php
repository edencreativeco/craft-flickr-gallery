<?php
    
namespace edencreative\craftflickrgallery\services;

use Craft;
use craft\base\Component;
use edencreative\craftflickrgallery\assets\GalleryAssetBundle;
use edencreative\craftflickrgallery\base\FlickrServer;
use edencreative\craftflickrgallery\Plugin;
use League\OAuth1\Client\Credentials\TokenCredentials;

/**
 * @property    FlickrService   $flickrService  Flickr Service
 * @property    bool    $isConnected
 * 
 */
class TwigService extends Component {


    /**
     * @var FlickrService flickrService
     */
    private $_flickrService;


    /**
     * Check whether the plugin has flickr credentials set up
     * @return  bool
     */
    public function getIsConnected(): bool {
        return Plugin::$plugin->hasFlickrCredentials;
    }

    /**
     * @param   array   $options    optional properties: size, page, perpage, 
     * @return  object|null
     */
    public function getPhotos(array $options = []): ?object {

        $data = $this->flickrService->getPhotos($options);
        if (!$data) return null;


        return (object)[
            'photos' => $data->photos?->photo ?? [],
            'pagination' => $this->getPaginationData($data->photos)
        ];
    }

    /**
     * @param   array   $options    optional properties: page, perpage, 
     * @return  object|null
     */
    public function getPhotosets(array $options = []): ?object {
        
        $data = $this->flickrService->getPhotosets($options);
        if (!$data) return null;

        return (object)[
            'albums' => $data->photosets?->photoset ?? [],
            'pagination' => $this->getPaginationData($data->photosets)
        ];
    }

    /**
     * @param   int     $id
     * @param   array   $options    optional properties: size, page, perpage, 
     * @return  ?object
     */
    public function getPhotoset(int $id, array $options = []): ?object {
        
        $data = $this->flickrService->getPhotoset($id, $options);

        $photoset = $data?->photoset ?? null;
        if (!$photoset) return null;

        // add srcset
        $photos = array_map(function($photo) {
            $photo->srcset = $this->getSrcset($photo->sizes);
            return $photo;
        }, $photoset->photo);


        return (object)[
            'id' => $photoset->id,
            'primary' => $photoset->primary,
            'owner' => $photoset->owner,
            'ownername' => $photoset->ownername,
            'title' => $photoset->title,
            'photos' => $photos,
            'pagination' => $this->getPaginationData($photoset)
        ];
    }

    /**
     * @param   int     $id
     * @return  ?object
     */
    public function getPhotosetDetails(int $id): ?object {
        
        $data = $this->flickrService->getPhotosetDetails($id);

        $photoset = $data->photoset;
        if (!$photoset) return null;


        $photoset->title = $photoset->title?->_content;
        $photoset->description = $photoset->description?->_content;
        $photoset->cover = $this->flickrService->getCoverImageUrl($photoset);

        return $photoset;
    }



    public function getFlickrService(): FlickrService
    {
        if (!$this->_flickrService) {
            $this->_flickrService = new FlickrService();
        }

        return $this->_flickrService;
    }




    // /**
    //  * Render a gallery view for an album id
    //  * @param   int     $albumId
    //  * @param   array   $options    optional properties: size, page, perpage, 
    //  */
    // public function gallery(int $albumId, array $options = []) {
    //     // TODO - README options
    //     $view = Craft::$app->getView();
    //     $view->registerAssetBundle(GalleryAssetBundle::class);

    //     // [
    //     //     'size' => $options['size'] ?? null,
    //     //     'page' => $options['page'] ?? null,
    //     //     'perpage' => $options['perpage'] ?? null,
    //     // ]

    //     $data = $this->getPhotoset($albumId, $options);

    //     if ($data) {

    //         echo $view->renderTemplate('craft-flickr-gallery/_includes/photoset-gallery', [
    //             'photoset' => $data,
    //             'options' => $options
    //         ]);

    //     } else {

    //         // ERROR
    //         echo $view->renderTemplate('craft-flickr-gallery/_includes/error-block', [
    //             'errorMessage' => 'Could not load photo gallery',
    //         ]);

    //     }

    //     return;

    // }




    
    //=================================================//
    //================ Private Methods ================//
    //=================================================//
        

    /**
     * Get pagination data from flickr api return data
     * @param   ?object $data
     * @return  ?object
     */
    private function getPaginationData(?object $data): ?object {   
        return (object)[
            'page' => $data?->page,
            'pages' => $data?->pages,
            'perpage' => $data?->perpage,
            'total' => $data?->total,
        ];
    }

    /**
     * Get srcset from photo sizes
     */
    private function getSrcset(array $sizes) {
        $srcset = [];
        foreach($sizes as $key => $url) {
            if (!in_array($key, ['t', 'm', 'w', 'b', 'z', 'o'])) continue;
            $width = FlickrService::PHOTO_SIZES[$key] ?? 2000;
            $srcset[] = [
                'size' => $width,
                'url' => $url,
                'output' => "$url $width" . 'w'
            ];
        }

        usort($srcset, fn($a, $b) => $a['size'] > $b['size'] );

        return join( ", ", array_map(fn($row) => $row['output'], $srcset) );

    }

}
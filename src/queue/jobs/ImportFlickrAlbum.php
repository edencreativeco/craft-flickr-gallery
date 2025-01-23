<?php

namespace edencreative\craftflickrgallery\queue\jobs;

use Craft;
use craft\queue\BaseJob;
use edencreative\craftflickrgallery\Plugin;
use edencreative\craftflickrgallery\services\AssetsService;
use edencreative\craftflickrgallery\services\FlickrService;

class ImportFlickrAlbum extends BaseJob {

    const PER_PAGE = 50;

    /**
     * @var int $albumId
     */
    public int $albumId;

    /**
     * @var string $importSize
     */
    public string $importSize = 'original';


    /**
     * @var FlickrService $fs
     */
    private $fs;

    
    public function execute($queue): void {
        
        $photoset = $this->getPhotoset();
        $albumName = $photoset->title;
        
        $useAlbumName = Plugin::$plugin->siteSettings->albumNameAsSubfolder;
        $subpath = $useAlbumName ? str_replace('/', '-', $albumName) : "";
        
        $as = new AssetsService();
        $importFolder = $as->getImportLocationFolderId($subpath);
        
        $total = $photoset->total;
        $pages = $photoset->pages;
        $page = $photoset->page;
        $imported = 0;
        $complete = false;

        while (!$complete) {

            if (!count($photoset->photo)) {
                $complete = true;
                continue;
            }

            foreach ($photoset->photo as $photo) {
                try {
                    
                    $importUrl = $photo->original;
                    if ($this->importSize && !empty($photo->sizes[$this->importSize])) {
                        $importUrl = $photo->sizes[$this->importSize];
                    } else {
                        $this->importSize = 'original';
                    }

                    $ext = pathinfo($importUrl, PATHINFO_EXTENSION);

                    $newAsset = $as->saveFlickrImageAsAsset($photo->id, $importUrl, $photo->title . "." . $ext, $importFolder, [
                        'album' => $albumName,
                        'album_id' => $this->albumId,
                        'import_size' => $this->importSize,
                    ]);
                    $successes[] = $newAsset->id;
                } catch (\Exception $e) {
                    $errorMessage = $e->getMessage();
                    Plugin::error($errorMessage);
                    $errors[] = $errorMessage;
                }

                $imported++;

                $this->setProgress(
                    $queue,
                    $imported / $total,
                    Craft::t('app', '{step, number} of {total, number} images imported', [
                        'step' => $imported,
                        'total' => $total,
                    ])
                );
            }

            if ($page >= $pages) {
                $complete = true;
            } else {
                $page++;
                $photoset = $this->getPhotoset($page);
            }

        }


    }


    protected function defaultDescription(): string
    {
        return Craft::t('app', 'Import album from Flickr');
    }

    private function getPhotoset($page = 1): object {

        if (!$this->fs) $this->fs = new FlickrService();

        $data = $this->fs->getPhotoset($this->albumId, [
            'perpage' => self::PER_PAGE,
            'page' => $page,
            'size' => 'original'
        ]);
        $photoset = $data->photoset;
        
        if (!$photoset) {

            Plugin::error("Invalid return data from Flickr");
            Plugin::error("Params: " . json_encode(['albumId' => $this->albumId, 'page' => $page]));
            Plugin::error(json_encode($data));

            throw new \Error("Invalid return data from Flickr");
        }

        return $photoset;
    }

}

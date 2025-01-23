<?php

namespace edencreative\craftflickrgallery\queue\jobs;

use Craft;
use craft\queue\BaseJob;
use edencreative\craftflickrgallery\Plugin;
use edencreative\craftflickrgallery\services\AssetsService;
use edencreative\craftflickrgallery\services\FlickrService;

class ImportFlickrPhotos extends BaseJob {

    /**
     * @var int[] $photoIds
     */
    public array $photoIds;

    /**
     * @var int|null $albumId
     */
    public ?int $albumId = null;

    /**
     * @var string|null $albumName
     */
    public ?string $albumName = null;

    /**
     * @var string $importSize
     */
    public string $importSize = 'original';


    // The following properties can be used to get results from an immediately executed job 

    /**
     * @var int[]   $importedIds
     */
    public array $importedIds = [];

    /**
     * @var int[]   $importedFlickrIds
     */
    public array $importedFlickrIds = [];

    /**
     * @var array   $importErrors
     */
    public array $importErrors = [];

    
    public function execute($queue): void {

        if (!$this->photoIds) return;

        $as = new AssetsService();        
        $fs = new FlickrService();

        $useAlbumName = Plugin::$plugin->siteSettings->albumNameAsSubfolder;
        $subpath = $useAlbumName && !!$this->albumName ? str_replace('/', '-', $this->albumName) : "";
        $importFolder = $as->getImportLocationFolderId($subpath);

        $total = count($this->photoIds);
        $counter = 0;
        $imported = 0;
        $errors = 0;

        foreach( $this->photoIds as $id ) {

            // update progress
            $this->setProgress(
                $queue,
                $counter / $total,
                Craft::t('app', '{step, number} of {total, number} images imported, {skipped, number} skipped', [
                    'step' => $imported,
                    'total' => $total,
                    'skipped' => $errors,
                ])
            );

            
            try {

                $photo = $fs->getCachedPhotoData($id) ?? $fs->getPhotoInfo($id);
                if (!$photo) throw new \Exception("photo with id $id not found");

                Plugin::info(json_encode($photo));

                $importUrl = $photo->original;
                if ($this->importSize && !empty($photo->sizes[$this->importSize])) {
                    $importUrl = $photo->sizes[$this->importSize];
                } else {
                    $this->importSize = 'original';
                }

                $ext = pathinfo($importUrl, PATHINFO_EXTENSION);

                $newAsset = $as->saveFlickrImageAsAsset($photo->id, $importUrl, $photo->title . "." . $ext, $importFolder, [
                    'album' => $this->albumName,
                    'album_id' => $this->albumId,
                    'import_size' => $this->importSize,
                ]);

                $this->importedIds[] = $newAsset->id;
                $this->importedFlickrIds[] = $id;
                $imported++;

            } catch (\Exception $e) {

                $errorMessage = $e->getMessage();

                Plugin::error($errorMessage);
                $this->importErrors[] = $errorMessage;
                $errors++;

            }

            $counter++;

        }



    }


    protected function defaultDescription(): string
    {
        return Craft::t('app', 'Import album from Flickr');
    }

}

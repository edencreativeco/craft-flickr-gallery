<?php
    
namespace edencreative\craftflickrgallery\services;

use Craft;
use craft\base\Component;
use craft\elements\Asset;
use craft\fields\Assets as AssetsField;
use craft\helpers\Db;
use edencreative\craftflickrgallery\db\Table;
use edencreative\craftflickrgallery\Plugin;

class AssetsService extends Component {

    /**
     * Save flickr image data as a new asset
     * @param   int         $photoId
     * @param   string      $imageUrl
     * @param   string      $filename
     * @param   int|null    $folderId   defaults to the import location defined in the site settings
     * @param   array       $flickrParams   optional. Any additional params to save to the flickr assets db (album, album_id)
     * @param   array       $metadata   optional. Include things like title, description, tags
     * @return  Asset
     * @throws  Exception
     */
    public function saveFlickrImageAsAsset(int $photoId, string $imageUrl, string $filename, ?int $folderId = null, array $flickrParams = [], array $metadata = []): Asset {

        if (!$folderId) $folderId = $this->getImportLocationFolderId();

        $tempPath = Craft::$app->getPath()->getTempPath() . '/' . $filename;
        file_put_contents($tempPath, file_get_contents($imageUrl));

        $asset = new Asset([
            'tempFilePath' => $tempPath,
            'filename' => $filename,
            'newFolderId' => $folderId,
            // 'volumeId' => $folderId,
        ]);

        $asset->setFieldValues($metadata);


        if (!Craft::$app->elements->saveElement($asset)) {
            Craft::error('Failed to save asset: ' . print_r($asset->getErrors(), true));
            Plugin::error('Failed to save asset: ' . print_r($asset->getErrors(), true));
            throw new \Exception("Failed to save Flickr asset", 500);
        }

        $albumName = $flickrParams['album'] ?? null;
        if (!is_string($albumName)) $albumName = null;

        $albumId = intval($flickrParams['album_id'] ?? null) ?: null;

        // add to our flickr assets db
        Db::insert(Table::FLICKR_ASSETS, [
            'id' => $asset->id,
            'photo_id' => $photoId,
            'album' => $albumName,
            'album_id' => $albumId,
            'import_size' => $flickrParams['import_size'] ?? 'original'
        ]);

        return $asset;

    }

    /**
     * @param   string|null $subfolder  optional subpath from the import folder defined in the settings
     * @return  int
     */
    public function getImportLocationFolderId(?string $subfolder = ""): int {

        // Get settings from site ID
        $settingsData = Plugin::$plugin->siteSettings;

        $path = $settingsData->flickrAssetImportPathSubpath . '/' . $subfolder;

        $pseudoField = new AssetsField([
            'name' => 'Flickr Gallery Site Settings Upload Location Field',
            'defaultUploadLocationSource' => $settingsData->flickrAssetImportPathSource,
            'defaultUploadLocationSubpath' => $path
        ]);

        $folderId = $pseudoField->resolveDynamicPathToFolderId();

        return $folderId;
    }

}
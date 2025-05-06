<?php
    
namespace edencreative\craftflickrgallery\elements\db;

use craft\elements\db\AssetQuery;
use craft\helpers\Db;
use edencreative\craftflickrgallery\db\Table;

class FlickrAssetQuery extends AssetQuery {

    
    public mixed    $flickrPhotoId = null;
    public mixed    $flickrAlbum = null;
    public mixed    $flickrAlbumId = null;

    /**
     * query by flickr photo id
     * @param   mixed
     * @return  self
     * @uses    $flickrPhotoId
     */
    public function flickrPhotoId(mixed $value = null) {
        $this->flickrPhotoId = $value;
        return $this;
    }

    /**
     * Search for the flickr album
     * @param   mixed
     * @return  self
     * @uses    $flickrAlbum
     */
    public function flickrAlbum(mixed $value = null) {
        $this->flickrAlbum = $value;
        return $this;
    }

    /**
     * query by flickr album id
     * @param   mixed
     * @return  self
     * @uses    $flickrAlbumId
     */
    public function flickrAlbumId(mixed $value = null) {
        $this->flickrAlbumId = $value;
        return $this;
    }



    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {

        $this->joinElementTable(Table::FLICKR_ASSETS);

        $isValid = parent::beforePrepare();

        $addSelect = [
            Table::FLICKR_ASSETS . '.photo_id AS flickr_photo_id',
            Table::FLICKR_ASSETS . '.album_id AS flickr_album_id',
            Table::FLICKR_ASSETS . '.album AS flickr_album',
            Table::FLICKR_ASSETS . '.import_size AS flickr_import_size',
        ];

        $this->subQuery->addSelect($addSelect);
        $this->query->addSelect($addSelect);

        if ($this->flickrPhotoId) {
            $this->subQuery->andWhere(Db::parseParam(Table::FLICKR_ASSETS . '.photo_id', $this->flickrPhotoId));
        }

        if ($this->flickrAlbum) {
            $this->subQuery->andWhere(Db::parseParam(Table::FLICKR_ASSETS . '.album', $this->flickrAlbum));
        }

        if ($this->flickrAlbumId) {
            $this->subQuery->andWhere(Db::parseParam(Table::FLICKR_ASSETS . '.album_id', $this->flickrAlbumId));
        }

        return $isValid;

    }
}
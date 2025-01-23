<?php

namespace edencreative\craftflickrgallery\db;


/**
 * This class provides constants for defining flickr-gallery table names. Do not use these in migrations.
 */
abstract class Table {

    const SITE_SETTINGS = '{{%flickr-gallery_site-settings}}';

    const FLICKR_TOKENS = '{{%flickr_tokens}}';
    const FLICKR_ASSETS = '{{%flickr-gallery_assets}}';

}
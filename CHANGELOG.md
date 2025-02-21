# Release Notes for craft-flickr-gallery

## 1.0.3 - 2025-02-21

### Changed
- New folder names are now sanitized before being created
- AssetService::saveFlickrImageAsAsset no longer requires the filename parameter. The default filename will be pulled from the url where the image was fetched from.
- Default pagination size in Flickr Assets index changed from 100 to 50 to help reduce potentially large quantity of image transforms

### Fixed
- Fixed bug where invalid folder names could be created from imported albums
- Fixed bug where different images with matching titles could not be imported
- Corrected crumbs in cp templates


## 1.0.2 - 2025-01-31

### Fixed
- Fixed bug where non-admins could not view plugin in cp nav


## 1.0.1 - 2025-01-31

### Fixed
- Fixed issue where db migrations were missing on installation


## 1.0.0 - 2025-01-31
- Initial release

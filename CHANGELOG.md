# Release Notes for craft-flickr-gallery

## 1.1.5 - 2025-05-06

### Fixed
- Fixed error "Element query executed before Craft is fully initialized" related to user permissions

## 1.1.3 - 2025-05-06

### Changed
- Renamed db tables to remove dashes

## 1.1.2 - 2025-03-19

### Fixed
- Fixed photo pagination bug in the asset modal import view

## 1.1.1 - 2025-03-14

### Fixed
- Applied plugin permission checks to admin templates and api endpoints
- The "Import from Flickr" button will no longer display on asset upload modals for users who do not have access to the Flickr Gallery plugin
- Improved error notifications in cp for failed api calls

## 1.1.0 - 2025-03-07

### Added
- Ability to import Flickr photos directly to an Asset Selector field
- queue\jobs\ImportFlickrPhotos will now track all asset IDs, including assets for flickr photos that have already been imported
- new body param "immediate" is now supported in the FlickrController::actionImportPhotos method, allowing a request to bypass the queue and execute immediately

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

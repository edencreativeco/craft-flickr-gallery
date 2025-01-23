# Flickr Gallery

Import photos directly from your Flickr account

## Requirements

This plugin requires Craft CMS 4.0.0 or later, and PHP 8.0.2 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “craft-flickr-gallery”. Then press “Install”.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require eden-creative/craft-flickr-gallery

# tell Craft to install the plugin
./craft plugin/install craft-flickr-gallery
```


## OAuth
1. Request an API key from Flickr here: https://www.flickr.com/services/apps/create/apply/
2. Go to the Plugin Settings, and input your username, api token, and api secret.
    Your username can be found in the url of your photos or albums on flickr (flickr/com/photos/&lt;username&gt;/...)
3. Save settings
4. Copy the callback url listed beneath the connect button
5. Open your App in the Flickr App Garden, Edit the workflow, and copy the callback url into the Callback URL field
6. Back in your plugin settings, Click the "Connect" button (if using a proxy, make sure you are currently logged into your local site via the proxy url used in your callback url)
7. Authorize your app, and you should be all set!





## Template Usage
Get the Flickr Album:

```
{% set album = craft.flickr.getPhotoset(entry.flickrAlbum) %}
```

Make sure an album was returned. Render original photos:

```
{% if album %}
    {% for photo in album.photos %}
        <img src="{{ photo.original }}"/>
    {% endfor %}
{% else %}
    <!-- error handling here -->
{% endif %}
```

Render different photo sizes (t, q, m, z, b, w):

```
{% if album %}
    {% for photo in album.photos %}
        <img src="{{ photo.sizes.t }}"/>
    {% endfor %}
{% else %}
    <!-- error handling here -->
{% endif %}
```
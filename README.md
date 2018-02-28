# IIIF Toolkit / IIIF API Synchronization Bridge

This plugin enables synchronization between the [IIIF Toolkit Omeka Classic](https://github.com/utlib/IiifItems) plugin and [IIIF API](https://github.com/utlib/utl_iiif_api) by University of Toronto Libraries.

## System Requirements

* Omeka Classic 2.4 and up
* IIIF image server pointing to the Omeka installation's files/original directory (optional if you will only be importing content from existing manifests)
* [IIIF Toolkit](https://github.com/utlib/IiifItems) 1.1.0 and up
* [IIIF API](https://github.com/utlib/utl_iiif_api) 1.0.0 and up (does not have to be on the same server as Omeka)

## Installation

* Clone this repository to the ```plugins``` directory of your Omeka installation.
* Sign in as a super user.
* In the top menu bar, select "Plugins".
* Find "IIIF Toolkit / IIIF API Synchronization Bridge" in the list of plugins and select "Install".
* Enter the following settings:
    * IIIF API Root: The root URL to your IIIF API instance.
    * API Key: The access token returned from the ```/login``` endpoint in IIIF API after signing in.
    * Top-Level Collection Name (optional): All top-level IIIF collections and manifests will be synchronized as a child of this collection in IIIF API. If left empty, this plugin synchronizes with the top-level collection of IIIF API.
    * Top-Level Prefix (optional): A prefix to add before the numeric collection/item IDs before synchronizing with the API. This is needed if there are already items with numerical names in your IIIF API instance, or if multiple instances of Omeka running this plugin are synchronizing with it.
* Select "Save Changes" to continue.

## Forcing Synchronization

A "Push up" button appears in the admin-side views of IIIF-enabled collections and items. If a collection or item fails to synchronize, you can click this button to manually retry.

## License

IIIF Toolkit / IIIF API Synchronization Bridge is licensed under Apache License 2.0.

eZ Brightcove by Kaliop
=======================

About
-----

eZ Brightcove is an extension to eZ Publish that enables you to either
upload videos from eZ Publish into your Brightcove Media Library or pull in
videos from your Brightcove Media Library into eZ Publish.

Installation
------------

Please see the installation documentation inside [doc/INSTALL.md](https://github.com/kaliop/ezbrightcove/blob/master/doc/INSTALL.md) for how to
install this extension

Screenshots
-----------

Uploading a video from your computer to Brightcove directly from the eZ Publish
administration interface.

![](https://raw.github.com/kaliop/ezbrightcove/master/doc/screenshots/upload.png)

Editing a Brightcove Media object in the eZ Publish administration interface
with a video selected.

![](https://raw.github.com/kaliop/ezbrightcove/master/doc/screenshots/edit_with_video.png)

Browse and search your Brightcove Media Library straight from within the
editing interface.

![](https://raw.github.com/kaliop/ezbrightcove/master/doc/screenshots/browse_brightcove.png)

Browse videos uploaded to your web server from the administration interface and
upload them directly to Brightcove.

![](https://raw.github.com/kaliop/ezbrightcove/master/doc/screenshots/server.png)

Viewing a Brightcove Media object in the eZ Publish administration interface.

![](https://raw.github.com/kaliop/ezbrightcove/master/doc/screenshots/view.png)


Configuration
-------------

### Enabling/disabling "input types"

You can easily turn on/off different ways of uploading or accessing Brightcove
media. By default all 3 options (`Upload video`, `Source video from Brightcove`
and `Upload video from Server`) are all enabled.

If you want to turn of them off, override settings/ezbrightcove.ini and change
the `AvailableTypes` setting. The below example turns off all input types
except `Upload video`.

    file: settings/override/ezbrightcove.ini.append.php:
    [VideoInputTypes]
    AvailableTypes[]
    AvailableTypes[]=upload

### Upload video from Server

This input method is comes in handy if you already have a large media library
that you manage in some other way, e.g via FTP. eZ Brightcove can read videos
from a directory on your web server. This could be a directory on a shared
drive or a directory where videos are uploaded via some other means, e.g ftp,
rsync, scp, etc.

By default the `Upload video from Server` input type is available, but you need
to specify a directory on your web server that contains videos. This is done by
overriding settings/ezbrightcove.ini and specifying the `RootDirectory`
setting. In the example below we tell eZ Brightcove to look in
`/mnt/shared/videos/` for videos.

    file settings/override/ezbrightcove.ini.append.php:
    [ServerFileBrowse]
    RootDirectory=/mnt/shared/videos


Extending eZ Brightcove
-----------------------

eZ Brightcove was developed with extensibility in mind. There's two major ways
you can use to extend this extension: creating new input types or swapping out
PHP classes.

### Creating a new input types

eZ Brightcove comes with 3 different ways you can tell eZ Publish and
Brightcove about your media (input types). You can upload a video from your
local computer, Browse Brightcove for a video or Browse a directory on your web
server for a video.

If these do not fit your needs you can create your own input type. Input types
consist of a few different parts:

- Templates for viewing and editing
- A PHP class that contains all the logic (validation, storing of data, etc).
  This is basically a mini eZ Datatype.
- A SQL table (or somewhere else) to store any data you might need

Input types are said to either "require processing" or not. Require processing
means that the video should be sent for processing to Brightcove. Brightcove
will then convert the video into a suitable format for the web. Both `Upload
video` and `Upload video from Server` are input types that requires procesing
and hence eZ Brightcove will upload the video to Brightcove for processing.
`Source video from Brightcove` does not require processing as the videos are
already sourced from Brightcove and hence are in a web suitable format already.

To get started writing your own input type it's easiest to look at one of the
existing ones. `Source video from Brightcove` is a great starting point:

- [klpBcServerFileVideoInputType](https://github.com/kaliop/ezbrightcove/blob/master/classes/videoinputs/klpbcserverfilevideoinputtype.php)
- [kloBcServerFile (the persistent object)](https://github.com/kaliop/ezbrightcove/blob/master/classes/klpbcserverfile.php)
- [serverfile_content_edit.tpl](https://github.com/kaliop/ezbrightcove/blob/master/design/standard/templates/klpbc/video_inputs/serverfile_content_edit.tpl)
- [serverfile_content_edit_label.tpl](https://github.com/kaliop/ezbrightcove/blob/master/design/standard/templates/klpbc/video_inputs/serverfile_content_edit_label.tpl)
- [serverfile_content_view.tpl](https://github.com/kaliop/ezbrightcove/blob/master/design/standard/templates/klpbc/video_inputs/serverfile_content_view.tpl)

###  Swapping out classes

Key classes in eZ Brightcove are dynamically configured in
`settings/ezbrightcove.ini` and loaded in by `klpDiContainer`. If you want to
change some builtin behaviour you can substitue any class for your own. However
please be aware that we reserve the right to change how these classes work. We
do not guarantee that the classes will not change in a way that might break your
substitue classes in the future. This should be considered a temporary
workaround. Please submit pull requests or issues if you hit bugs or
limitations.


License
-------

Please see the license file [LICENSE](https://github.com/kaliop/ezbrightcove/blob/master/LICENSE) in the root of the extension for license
details.

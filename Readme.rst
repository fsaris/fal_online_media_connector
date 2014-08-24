
FAL: Online media connector (working name)
==========================================

The ame of this extension is to replace all normal image only fields by media fields. A "Text + Images" content element becomes a "Text + Media" and you got the same layout options as before.

The idea is to make it as easy as possible for editors to use all kind of media between there written content. So for example normal images like already possible with a default TYPO3 install but also YouTube video links or a presentation hosted on SlideShare.
And this with just selecting a existing media item from storage (fileadmin) or by providing a link to the online location which then internally gets transformed to a file (a custom container file) that's get added to you local file system with thumbnail support for previews and extended metadata support.


Current supported types:
------------------------
 - YouTube (.ytb)
 - Vimeo (.vimeo)


Types todo:
-----------
 - SoundCloud
 - SlideShare
 - ...


Status:
-------

The extension is still very alpha but it works :)


Requirements:
-------------
 - TYPO3 => 6.2.4


Todo:
-----

 - Replace core labels so you know you can use other files then images
 - Check if it makes sense to support http://oembed.com/ format



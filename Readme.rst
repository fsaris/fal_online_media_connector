
FAL: Online media connector (working title: proposal 'media_helper')
====================================================================

The ame of this extension is to replace all normal image only fields by media fields. A "Text + Images" content element becomes a "Text + Media" and you got the same layout options as before.

The idea is to make it as easy as possible for editors to use all kind of media between there written content. So for example normal images like already possible with a default TYPO3 install but also YouTube video links or a presentation hosted on SlideShare.
And this with just selecting a existing media item from storage (fileadmin) or by providing a link to the online location which then internally gets transformed to a file (a custom container file) that's get added to you local file system with thumbnail support for previews and extended metadata support.


.. figure:: Documentation/Assets/add-new-media-to-ttcontent-element.png
   :alt: Add new media directly to your content element
   :align: center

   Add new media directly to your content element

.. figure:: Documentation/Assets/add-existing-media-to-ttcontent-element.png
   :alt: Select already uploaded/added media element and add it to your content element
   :align: center

   Select already uploaded/added media element and add it to your content element


Current supported types:
------------------------
- YouTube (.ytb)
- Vimeo (.vimeo)


Features:
---------

**Editors**

- Possibility to select supported media items just like normal images from element browser
- Possibility to supply the link to a media to use direct in content element BE form.
  The media item gets a preview thumbnail + title and caption fields just like a image.

**Technical**

- Thumbnail/static image preview support
- MediaViewHelper to have a generic ViewHelper to show all supported media item types
- By default the ImageViewHelper is Xclassed so the new file types are supported out of the box
- Hook render_singleMediaElement of css_styled_content is used to render online media for with TypoScript
- Possibility to register your own Online Media item types (see ext_localconf.php for an example how to use the register)


Status:
-------

The extension is still very alpha but it works :)


Requirements:
-------------
- TYPO3 => 6.2.4


Todo:
-----

- Add support for more types/online resources
- Replace core labels so you know you can use other files then images
- Check if it makes sense to support http://oembed.com/ format



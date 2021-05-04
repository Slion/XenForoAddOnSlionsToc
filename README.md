# Table Of Content Add-On for XenForo 2

# Features
- Provides the following BB codes:
  - TOC: For table of content placement.
  - H1, H2, H3, H4, H5, H6: For various headings level.  
- Provides editor buttons for headings from level one to four and the table of content itself.
- Supports WYSIWYG editor.
- Supports named anchor so you can reorganize headings in your post without worrying about broken links. Just do `[H1=anchor name]My heading[/H1]`

# Demo
Visit [slions.net] to see what it looks like.

# Release process

Go to the root of your XenForo installation and set the new release version by running:

`php cmd.php xf-addon:bump-version Slions/Toc --version-id 2020100`

Generate the release archive using the following command:

`php cmd.php xf-addon:build-release Slions/Toc`

The generated ZIP file can be found in the following folder:

`/src/addons/Slions/Toc/_releases`

# Resources

- [Add-on available on XenForo.com]
- [XenForo Development Tools]
- [TOC BB code implementation]
- [Porting TOC to XF2]
- [Render Custom BB code for WYSIWYG editor]
- [Anchor and TOC thread on XenForo]
- [Modifying Froala editor options]

[slions.net]: https://slions.net/resources/fulguris.10/
[XenForo Development Tools]: https://xenforo.com/docs/dev/development-tools
[Render Custom BB code for WYSIWYG editor]: https://xenforo.com/community/threads/parse-custom-bbcode-in-editorhtml-cant-add-new-tags.147361/
[Add-on available on XenForo.com]: https://xenforo.com/community/resources/slions-table-of-content.8222/
[Porting TOC to XF2]: https://xenforo.com/community/threads/porting-toc-bb-code-add-on-for-xf2-1.178041/#post-1490422
[Anchor and TOC thread on XenForo]: https://xenforo.com/community/threads/bbcode-for-anchor-and-toc.171540/#post-1490421
[TOC BB code implementation]: https://xenforo.com/community/threads/toc-bb-code-add-on-implementation.127502/
[Modifying Froala editor options]: https://xenforo.com/community/threads/modifying-froala-editor-options.161305/
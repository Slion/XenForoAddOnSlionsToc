<?php

namespace Slions\Toc\XF\Html\Renderer;

#use XF\BbCode\Traverser;
#use XF\Str\Formatter;
#use XF\Template\Templater;
#use XF\Util\Arr;

/**
 * This class is doing convertion from HTML to BbCode in our Froala editor when user switches from WYSIWYG to raw BB code editor 
 * That's also obviously used when saving from our WYSIWIG editor to obtain the raw text which is stored in our database.
 */
class BbCode extends XFCP_BbCode
{

	/**
	 * Handles heading tags.
	 * This only works because we override that existing heading handler.
	 * 
	 * @param string $text Child text of the tag
	 * @param Tag $tag HTML tag triggering call
	 *
	 * @return string
	 */
	public function handleTagH($text, \XF\Html\Tag $tag)
	{
		//return parent::handleTagH($text,$tag);	

		// Probably just to apply basic formatting: italic, bold, underline and such
		// Yes it looks like this allows italic in our header to be properly translated from HTML to BbCode
		// However we don't yet support doing the translation from BbCode back to HTML
		// Somehow that was removed from XenForo 2.2.4 
		//$text = $this->renderCss($tag, $text);

		$id = $tag->attribute('data-id');
		// As BB code are usually displayed in uppercase	
		$tagName = strtoupper($tag->tagName());		

		if (empty($id))
		{
			// No anchor id yet, build one then
			// That means that when saving from WYSIWYG all headings which do not have named anchor will get one based on their current text content
			// It is in theory still possible to only have anchor as empty and thus using incremental id if never using WYSIWYG, that should however hardly ever be the case anymore.
			// It also means that if the user changes the text or anchor name from raw editor they will be out of sync which could get very confusing
			// That's ok though I guess we can live with that
			// To generate our anchor name first URL encode our text and then throw away any URL encoded character that would look like garbadge in the address.
			// That will notably discard emoji and other unicode characters. If the result is empty we don't care it will still receive incremental id at render time then. 
			$anchorid = preg_replace('/%[0-9A-F]{2}/', '', urlencode($text));		
			return '[' . $tagName . ' id=\'' . $anchorid . "']" . $text . "[/". $tagName ."]";
		}
		else
		{
			return '[' . $tagName . ' id=\'' . $id . "']" . $text . "[/". $tagName ."]";
		}		
	}

}



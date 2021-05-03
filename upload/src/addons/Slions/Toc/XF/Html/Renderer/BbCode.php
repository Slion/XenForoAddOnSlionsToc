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
		$text = $this->renderCss($tag, $text);	

		$id = $tag->attribute('data-id');
		// As BB code are usually displayed in uppercase	
		$tagName = strtoupper($tag->tagName());		

		if (empty($id))
		{
			return '[' . $tagName . ']' . $text . "[/". $tagName ."]";
		}
		else
		{
			return '[' . $tagName . '=' . $id . ']' . $text . "[/". $tagName ."]";
		}		
	}

}



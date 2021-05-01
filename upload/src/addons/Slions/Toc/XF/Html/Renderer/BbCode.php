<?php

namespace Slions\Toc\XF\Html\Renderer;

#use XF\BbCode\Traverser;
#use XF\Str\Formatter;
#use XF\Template\Templater;
#use XF\Util\Arr;

/**
 * This class is doing convertion from HTML to BbCode in our Froala editor when user switches from WYSIWYG to raw BB code editor 
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



<?php

namespace Slions\Toc;

#use XF\BbCode\Traverser;
#use XF\Str\Formatter;
#use XF\Template\Templater;
#use XF\Util\Arr;

class BbCode
{

	public static function handleTagH($tagChildren, $tagOption, $tag, array $options, \XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		\XF::dump("handleTagH");	
		\XF::dump($tagChildren);
		\XF::dump($tagOption);
		\XF::dump($tag);
		\XF::dump($options);
		\XF::dump($renderer);

		//\XF::dumpSimple($var);

		//return "<" . $tag["tag"] . ">$tagChildren[0]</" . $tag["tag"] . ">";

		return "<$tag[tag]>$tagChildren[0]</$tag[tag]>";

	}


	public static function handleTagTOC($tagChildren, $tagOption, $tag, array $options, \XF\BbCode\Renderer\AbstractRenderer $renderer)
	{

		\XF::dump("handleTagTOC");
		\XF::dump($tagChildren);
		\XF::dump($tagOption);
		\XF::dump($tag);
		\XF::dump($options);
		\XF::dump($renderer);

		//\XF::dumpSimple($var);

		//return "<" . $tag["tag"] . ">$tagChildren[0]</" . $tag["tag"] . ">";

		return "";

	}


}



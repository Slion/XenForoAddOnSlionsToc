<?php

namespace Slions\Toc;

#use XF\BbCode\Traverser;
#use XF\Str\Formatter;
#use XF\Template\Templater;
#use XF\Util\Arr;

// For now I'm pretty sure we relly on our TOC element to come before any Hx element.
// Consider testing and fixing this at some point.
class BbCode
{
	static $debug = false;


	private static function getTocId($entity)
	{
		
		if ($entity instanceof \XF\Entity\Post)
		{
			//\XF::dump("IS POST");
			// Get the post id containing this TOC
			return $entity->post_id;
		}
		else if ($entity instanceof \XFRM\Entity\ResourceUpdate)
		{
			return $entity->resource_id;
		}
		else
		{
			\XF::dump("SlionsToc: unsupported entity");	
			return 0;
		}		
	} 

	// Tells if we are currently rendering thread preview.
	// We typically skip TOC rendering for thread preview.
	private static function isContextThreadPreview($renderer)
	{
		return $renderer->getRules()->getSubContext() == "thread_preview";
	}


	public static function handleTagH($tagChildren, $tagOption, $tag, array $options, \XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		if (BbCode::$debug)
		{
			\XF::dump("handleTagH");	
			\XF::dump($tagChildren);
			\XF::dump($tagOption);
			\XF::dump($tag);
			\XF::dump($options);
			\XF::dump($renderer);	
		}

		
		$entity = $options["entity"];

		if ($entity==null)
		{
			// Defensive
			\XF::dump("SlionsToc: no entity");			
			// Still show a header without id
			return "<$tag[tag]>$tagChildren[0]</$tag[tag]>";
		}

		$currentPostId = BbCode::getTocId($entity);

		// Initialize our output 		
		$output = "";				
		$text = $tagChildren[0];

		if (BbCode::isContextThreadPreview($renderer))
		{
			// Most likely rendering for preview from thread list
			// Forcing smaller header then
			$output .= '<b>' . $text . '</b><br />';
		}
		else
		{
			$id = $currentPostId . "-" . $GLOBALS['slionsHeaderCount'];// TODO: Use depth instead of count?
			//$output .= '<' . $tag['tag'] .' class="block-header" id="'. $id .'">\n::before\n<a>' . $text . '</a>\n::after\n</' . $tag['tag'] .'>';
			// Complex elements was needed to get the target to work well with theme floating top bar.
			// This was taken from forum list categories
			//$output .= "<span class='u-anchorTarget' id='$id'></span><div class='block-container'><$tag[tag] class='block-header'><a href='#$id'>$text</a></$tag[tag]></div>";			
			//$output .= "<span class='u-anchorTarget' id='$id'></span><$tag[tag] class='block-header'><a href='#$id'>$text</a></$tag[tag]>";
			$output .= "<span class='u-anchorTarget' id='$id'></span><$tag[tag]><a href='#$id'>$text</a></$tag[tag]>";
		}	
		
		
		// Increment our header index
		$GLOBALS['slionsHeaderCount']++;
		
		return $output;			


	}


	public static function handleTagTOC($tagChildren, $tagOption, $tag, array $options, \XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		BbCode::resetToc();

		if (BbCode::isContextThreadPreview($renderer))
		{
			// Most likely rendering for preview from thread list
			// Not displaying TOC then
			return "";
		}

		if (BbCode::$debug)
		{			
			\XF::dump("handleTagTOC");
			\XF::dump($tagChildren);
			\XF::dump($tagOption);
			\XF::dump($tag);
			\XF::dump($options);
			\XF::dump($renderer);
			//\XF::dump($renderer->getRules()->getContext());
		}
		//\XF::dumpSimple($var);

		//return "<" . $tag["tag"] . ">$tagChildren[0]</" . $tag["tag"] . ">";

		$entity = $options["entity"];

		if ($entity==null)
		{
			// Defensive
			\XF::dump("SlionsToc: no entity");
			return "";
		}

		$currentPostId = BbCode::getTocId($entity);

		// That works at least for resource and post
		$rawPostText = $entity->message;;

				
		$headerDepth = array
			(
				'h1' => 1,
				'h2' => 2,
				'h3' => 3,
				'h4' => 4,
				'h5' => 5,
				'h6' => 6
			);
		
		$output = "";
		//$output .= $renderer->getRules()->getContext() . "-" . $renderer->getRules()->getSubContext() . "<br />";

		
		//$output .= "<br>VIEW PARAMS<br>";
	    //$output .= $this->dumpRec($viewParams,0,20);
		//return $output;
		
		// Parse our headers out of the raw text of our post
		$headers=array(); // This will contain the output of our parsing
		preg_match_all('~\[h([1-6])](.*?)\[/h\1\]~',$rawPostText,$headers,PREG_SET_ORDER);
		
		$count = 0;
				
		//$output .= $this->dumpRec($headers,0,20);
				
		foreach ($headers as $header)
		{
			// Create our new TOC entry
			$tocEntry = new Entry();
			$tocEntry->mText = $header[2];
			$tocEntry->mDepth = $headerDepth['h'.$header[1]];
			// We should use getHeaderId but that should be more optimized
			$tocEntry->mId = $currentPostId . "-" . $count;
			$tocEntry->mIndex = $count;
			// We have a new header		
			$GLOBALS['slionsToc']->addTocEntry($tocEntry);	
			$count++;
		}
		
		$output .= $GLOBALS['slionsToc']->renderHtmlToc(0,8);
		//return get_class($renderer) . $output;
		return $output;
	}



	/**
	 * Between posts we need to reset our TOC
	 */
	private static function resetToc()
	{
		$GLOBALS['slionsToc'] = new Entry();
		$GLOBALS['slionsHeaderCount'] = 0;
	}


}



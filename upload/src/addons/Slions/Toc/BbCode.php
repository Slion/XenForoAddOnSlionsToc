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

	private static function doDebug(array $options)
	{
		// Confusingly user is not the logged user but the post/entity author.
		//if (BbCode::$debug && $options['user']->user_id == 1)
		// For now just specify the post ID you want to debug to avoid spamming users on production site
		if (BbCode::$debug /*&& $options['entity']->post_id == 89*/)
		{
			return true;
		}

		return false;
	}

	/**
	 * Fetch our TOC root entry from the given id.
	 */
	private static function getToc($aTocId)
	{		
		return $GLOBALS['slionsToc'.$aTocId];
	} 

	/**
	 * 
	 */
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
		// Initialize our output early on to allow for debug
		$output = "";


		//BbCode::createHeaderCountIfNeeded();

		if (BBCode::doDebug($options))
		{
			/*
			\XF::dump("handleTagH");	
			\XF::dump($tagChildren);
			\XF::dump($tagOption);
			\XF::dump($tag);
			\XF::dump($options);
			\XF::dump($renderer);	
			*/
		}				
		
		$entity = $options["entity"];

		if ($entity==null)
		{
			// Defensive
			\XF::dump("SlionsToc: no entity");
			// Still show a header without id
			return "<$tag[tag]>$tagChildren[0]</$tag[tag]>";
		}

		$tocId = BbCode::getTocId($entity);
		//$output .= "This ID:" . BbCode::getToc($tocId)->mNextHeaderId . "<br/>";
		//$output .= "TOC count:" . BbCode::getToc($tocId)->countEntries() . "<br/>";
		if (BbCode::buildToc($tocId,$entity) && BBCode::doDebug($options))
		{
			$output .= "H rebuilt TOC<br/>";
		}

		$text = $tagChildren[0];

		if (BbCode::isContextThreadPreview($renderer))
		{
			// Most likely rendering for preview from thread list
			// Forcing smaller header then
			$output .= '<b>' . $text . '</b><br />';
		}
		else
		{
			$id = $tocId . "-" . BbCode::getToc($tocId)->mNextHeaderId;// TODO: Use depth instead of count?
			//$output .= '<' . $tag['tag'] .' class="block-header" id="'. $id .'">\n::before\n<a>' . $text . '</a>\n::after\n</' . $tag['tag'] .'>';
			// Complex elements was needed to get the target to work well with theme floating top bar.
			// This was taken from forum list categories
			//$output .= "<span class='u-anchorTarget' id='$id'></span><div class='block-container'><$tag[tag] class='block-header'><a href='#$id'>$text</a></$tag[tag]></div>";			
			//$output .= "<span class='u-anchorTarget' id='$id'></span><$tag[tag] class='block-header'><a href='#$id'>$text</a></$tag[tag]>";
			$output .= "<span class='u-anchorTarget' id='$id'></span><$tag[tag]><a href='#$id'>$text</a></$tag[tag]>";
		}	
				
		// Increment our header index
		BbCode::getToc($tocId)->mNextHeaderId++;
		//$output .= "Next ID:" . BbCode::getToc($tocId)->mNextHeaderId . "<br/>";
		
		return $output;			
	}


	public static function handleTagTOC($tagChildren, $tagOption, $tag, array $options, \XF\BbCode\Renderer\AbstractRenderer $renderer)
	{		
		// Initialize our output early on to allow for debug
		$output = "";

		if (BbCode::isContextThreadPreview($renderer))
		{
			// Most likely rendering for preview from thread list
			// Not displaying TOC then
			return "";
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

		$tocId = BbCode::getTocId($entity);		
		if (BbCode::buildToc($tocId,$entity) && BBCode::doDebug($options))
		{
			$output .= "TOC rebuilt<br/>";
		}
		
		$output .= BbCode::getToc($tocId)->renderHtmlToc(0,8);
		//return get_class($renderer) . $output;

		if (BBCode::doDebug($options))
		{			
			//$output .= print_r($renderer,true);
			//$output .= print_r($renderer->getRules()->getSubContext(),true);
			//$output .= print_r($renderer->getRules()->getContext(),true);
			//\XF::dump("handleTagTOC");
			//\XF::dump($tagChildren);
			//\XF::dump($tagOption);
			//\XF::dump($tag);
			//\XF::dump($options);
			//\XF::dump($renderer);
			//\XF::dump($GLOBALS);			
			//\XF::dump($renderer->getRules()->getContext());
		}

		return $output;
	}

	/**
	 * Build our TOC from specified id and raw entity text.
	 */
	private static function buildToc($aTocId, $aEntity)
	{
		if (array_key_exists('slionsToc'.$aTocId, $GLOBALS)
		// If the TOC was already created but there was an edit we need to reset it
		// Check if all headers have already been accounted for 
		//&& !BbCode::getToc($aTocId)->isComplete()
		// If our entity was edited since generated our TOC it is not valid anymore
		&& BbCode::getToc($aTocId)->mLastEditDate == $aEntity->last_edit_date)
		{
			// This TOC was already created
			return false;
		}

		$toc = new Entry();
		$GLOBALS['slionsToc'.$aTocId] = $toc;
		//$toc->mLastEditDate = $aEntity->getValue('last_edit_date');
		$toc->mLastEditDate = $aEntity->last_edit_date;

		$headerDepth = array
			(
				'h1' => 1,
				'h2' => 2,
				'h3' => 3,
				'h4' => 4,
				'h5' => 5,
				'h6' => 6
			);
				
		//$output .= $options['user']->user_id  . "<br />"; 
		//$output .= $renderer->getRules()->getContext() . "-" . $renderer->getRules()->getSubContext() . "<br />";

		
		//$output .= "<br>VIEW PARAMS<br>";
	    //$output .= $this->dumpRec($viewParams,0,20);
		//return $output;
		
		// Parse our headers out of the raw text of our post
		$headers=array(); // This will contain the output of our parsing
		preg_match_all('~\[h([1-6])](.*?)\[/h\1\]~',$aEntity->message,$headers,PREG_SET_ORDER);
		
		$count = 0;
				
		//$output .= $this->dumpRec($headers,0,20);
				
		foreach ($headers as $header)
		{
			// Create our new TOC entry
			$tocEntry = new Entry();
			$tocEntry->mText = $header[2];
			$tocEntry->mDepth = $headerDepth['h'.$header[1]];
			// We should use getHeaderId but that should be more optimized
			$tocEntry->mId = $aTocId . "-" . $count;
			$tocEntry->mIndex = $count;
			// We have a new header		
			$toc->addTocEntry($tocEntry);	
			$count++;
		}

		return true;
	}


}



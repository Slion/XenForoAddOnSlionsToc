<?php

namespace Slions\Toc;

#use XF\BbCode\Traverser;
#use XF\Str\Formatter;
#use XF\Template\Templater;
#use XF\Util\Arr;

function endsWith($haystack, $needle) {
    return substr_compare($haystack, $needle, -strlen($needle)) === 0;
}


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
			//\XF::dump("SlionsToc: unsupported entity");	
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

		if (BBCode::doDebug($options))
		{
			//$output .= print_r($renderer,true) . "<br />";
			//$output .= $tagOption . "<br />";
			\XF::dump("handleTagH");	
			\XF::dump($tagChildren);
			\XF::dump($tagOption);
			\XF::dump($tag);
			\XF::dump($options);
			\XF::dump($renderer);	
			
			//$output .= 'Renderer type: ' . get_class($renderer) . "<br />";

			/*
			if ($renderer instanceof \Slions\Toc\XF\BbCode\Renderer\EditorHtml)
			{
				$output .= 'Context: ' . print_r($renderer->getRules()->getContext(),true) . "<br />";
				$output .= 'Sub context: ' . print_r($renderer->getRules()->getSubContext(),true) . "<br />";
			}
			*/


		}				
		
		$entity = $options["entity"];

		if ($entity==null)
		{
			// This is notably the case when rendering BBcodes help page: https://staging.slions.net/help/bb-codes/
			//\XF::dump("SlionsToc: no entity");
			// Still show a header without id
			//return "<$tag[tag]>$tagChildren[0]</$tag[tag]>";
		}

		$tocId = BbCode::getTocId($entity);
		//$output .= "This ID:" . BbCode::getToc($tocId)->mNextHeaderId . "<br/>";
		//$output .= "TOC count:" . BbCode::getToc($tocId)->countEntries() . "<br/>";
		if (BbCode::buildToc($tocId, $entity, $renderer, $options) && BBCode::doDebug($options))
		{
			$output .= "H rebuilt TOC<br/>";
		}

		$text = "";
		
		// See XF/BbCode/Renderer/Html.php renderTagList and renderTagTable functions to see how they do it.		
		// Render our children has needed, that notably makes sure styles like bold and italic are applied.
		// It also renders emojis as configured.
		$text = $renderer->renderSubTree($tagChildren, $options);
	

		if (BbCode::isContextThreadPreview($renderer))
		{
			// Most likely rendering for preview from thread list
			// Forcing smaller header then
			$output .= '<b>' . $text . '</b><br />';
		}
		else if($renderer instanceof \Slions\Toc\XF\BbCode\Renderer\EditorHtml)
		{
			// We are rendering in our WYSIWYG editor
			// Using custom data element we can store our anchor name for it to survive editor toggles between WYSIWYG and raw edit
			// See:  https://developer.mozilla.org/en-US/docs/Learn/HTML/Howto/Use_data_attributes

			if (empty($tagOption))
			{
				$output .=  "<$tag[tag]>$text</$tag[tag]>";
			}
			else
			{
				$output .=  "<$tag[tag] data-id='$tagOption'>$text</$tag[tag]>";		
			}
			
		}
		else
		{
			// Use count based id or anchor name if provided
			if (empty($tagOption))
			{
				$id = $tocId . "-" . BbCode::getToc($tocId)->mNextHeaderId;
			}
			else
			{
				$id = $tocId . "-" . urlencode($tagOption);
			}			
			
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

	/**
	 * 
	 */
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

		$tocId = BbCode::getTocId($entity);		
		if (BbCode::buildToc($tocId, $entity, $renderer, $options) && BBCode::doDebug($options))
		{
			$output .= "TOC rebuilt<br/>";
		}
		
		
		if($renderer instanceof \Slions\Toc\XF\BbCode\Renderer\EditorHtml)
		{
			// We are rendering in our WYSIWYG editor
			// Just preserve our TOC BbCode for now
			// TODO: Do something fancy like display Font Awesome icon?
			// Paragraph warp makes sure the TOC stays on its own line
			// That's not even used for now since we disabled rendering in HTML editor
			$output .=  "<p>[TOC][/TOC]</p>";
		}
		// TODO: preview context?
		else
		{
			$output .= BbCode::getToc($tocId)->renderHtmlToc(0,8);	
		}


		
		//return get_class($renderer) . $output;

		if (BBCode::doDebug($options))
		{			
			// That goes OOM, probably because of recursion
			//$output .= print_r($renderer,true) . "<br />";
			//$output .= 'Context: ' . print_r($renderer->getRules()->getContext(),true) . "<br />";
			//$output .= 'Sub context: ' . print_r($renderer->getRules()->getSubContext(),true) . "<br />";

			if ($renderer instanceof Slions\Toc\XF\BbCode\Renderer\EditorHtml)
			{
				$output .= 'Context: ' . print_r($renderer->getRules()->getContext(),true) . "<br />";
				$output .= 'Sub context: ' . print_r($renderer->getRules()->getSubContext(),true) . "<br />";
			}

			\XF::dump("handleTagTOC");


			\XF::dump($renderer->parser);
			\XF::dump($renderer->ruleSet);
			\XF::dump($tagChildren);
			\XF::dump($tagOption);
			\XF::dump($tag);
			\XF::dump($options);
			\XF::dump($renderer);
			\XF::dump($GLOBALS);			
			\XF::dump($renderer->getRules()->getContext());
		}

		return $output;
	}

	/**
	 * Build our TOC from specified id and raw entity text.
	 */
	private static function buildToc($aTocId, $aEntity, $aRenderer, $aOptions)
	{
		// Entity object check was need as we were chocking on it while toggling between WYSIWYG and raw editor.

		if (array_key_exists('slionsToc'.$aTocId, $GLOBALS)
		// If the TOC was already created but there was an edit we need to reset it
		// Check if all headers have already been accounted for
		// We could not get this working for some reason after inline edit so with use the date check below and that worked just fine 
		//&& !BbCode::getToc($aTocId)->isComplete()
		// If our entity was edited since generated our TOC it is not valid anymore
		&& (is_object($aEntity) && BbCode::getToc($aTocId)->mLastEditDate == $aEntity->last_edit_date))
		{
			// This TOC was already created and is still valid
			return false;
		}

		$toc = new Entry();
		$toc->renderer = $aRenderer;
		$toc->options = $aOptions;
		$GLOBALS['slionsToc'.$aTocId] = $toc;
		//$toc->mLastEditDate = $aEntity->getValue('last_edit_date');
		$content = "";
		
		if (is_object($aEntity))
		{
			$toc->mLastEditDate = $aEntity->last_edit_date;
			$content = $aEntity->message;
		}
		

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
				
		// Faking some render for our help page		
		if ($aEntity==null 
		&& endsWith($GLOBALS['_ENV']['REQUEST_URI'],'/help/bb-codes/'))
		{
			// Assuming help page I guess
			$content =<<<CNT
			[TOC][/TOC]
			[h1=anchor one]Heading One[/h1]
			[h2]Heading Two[/h2]
			[h3]Heading Three[/h3]
			[h4]Heading Four[/h4]
			[h5]Heading Five[/h5]
			[h6]Heading Six[/h6]
			CNT;
		}
		
		if (empty($content))
		{
			// Don't build our TOC if no content
			// That's just fine though that's notably the case in the editor
			return false;
		}

		// Parse our headers out of the raw text of our post
		$headers=array(); // This will contain the output of our parsing
		preg_match_all('~\[h([1-6])=?(.*?)](.*?)\[/h\1\]~i',$content,$headers,PREG_SET_ORDER);
		
		$count = 0;
				
		//$output .= $this->dumpRec($headers,0,20);
				
		foreach ($headers as $header)
		{
			// Create our new TOC entry
			$tocEntry = new Entry();
			$tocEntry->renderer = $aRenderer;
			$tocEntry->options = $aOptions;
			$tocEntry->mText = $header[3];
			$tocEntry->mDepth = $headerDepth['h'.$header[1]];
			// Workout our fragment id
			if (empty($header[2]))
			{
				// Use $count if no name provided
				$tocEntry->mId = $aTocId . "-" . $count;	
			}
			else 
			{
				// Otherwise use provided anchor name
				$tocEntry->mId = $aTocId . "-" . urlencode($header[2]);					
			}
			
			$tocEntry->mIndex = $count;
			// We have a new header		
			$toc->addTocEntry($tocEntry);	
			$count++;
		}

		return true;
	}


}



<?php

namespace Slions\Toc;

#use XF\BbCode\Traverser;
#use XF\Str\Formatter;
#use XF\Template\Templater;
#use XF\Util\Arr;

class BbCode
{
	static $debug = false;


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
			return "No entity";
		}

		$currentPostId = "";
		
		if ($entity instanceof \XF\Entity\Post)
		{
			//\XF::dump("IS POST");
			// Get the post id containing this TOC
			$currentPostId = $entity->post_id;
		}


		//\XF::dumpSimple($var);

		//return "<" . $tag["tag"] . ">$tagChildren[0]</" . $tag["tag"] . ">";

		//return "<$tag[tag]>$tagChildren[0]</$tag[tag]>";

		// Initialize our output 		
		$output = "";
					
		
		$text = $tagChildren[0];

		$id = $currentPostId . "-" . $GLOBALS['slionsHeaderCount'];// TODO: Use depth instead of count?

		$output .= '<' . $tag['tag'] .' id="'. $id .'">' . $text . '</' . $tag['tag'] .'>';
		
		// Increment our header index
		$GLOBALS['slionsHeaderCount']++;
		
		return $output;			


	}


	public static function handleTagTOC($tagChildren, $tagOption, $tag, array $options, \XF\BbCode\Renderer\AbstractRenderer $renderer)
	{

		BbCode::resetToc();

		if (BbCode::$debug)
		{
			\XF::dump("handleTagTOC");
			\XF::dump($tagChildren);
			\XF::dump($tagOption);
			\XF::dump($tag);
			\XF::dump($options);
			\XF::dump($renderer);
		}
		//\XF::dumpSimple($var);

		//return "<" . $tag["tag"] . ">$tagChildren[0]</" . $tag["tag"] . ">";

		$entity = $options["entity"];

		if ($entity==null)
		{
			// Defensive
			return "No entity";
		}

		$currentPostId = "";
		$rawPostText = "";

		if ($entity instanceof \XF\Entity\Post)
		{
			//\XF::dump("IS POST");
			// Get the post id containing this TOC
			$currentPostId = $entity->post_id;
			$rawPostText = $entity->message;
		}

		//\XF::dump($entity);
		//\XF::dump($currentPostId);
		//\XF::dump($rawPostText);


		// TODO: resource
		//else{}
		
		//return "<br />TOC<br />";
				
		$headerDepth = array
			(
				'h1' => 1,
				'h2' => 2,
				'h3' => 3,
				'h4' => 4,
				'h5' => 5,
				'h6' => 6
			);
		
		$output ="";

		
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



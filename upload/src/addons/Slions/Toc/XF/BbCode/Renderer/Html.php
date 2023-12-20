<?php

namespace Slions\Toc\XF\BbCode\Renderer;

#use XF\BbCode\Traverser;
#use XF\Str\Formatter;
#use XF\Template\Templater;
#use XF\Util\Arr;

/**
 * This class renders BbCode to HTML notably for page view.
 * It is calling custom BbCode callbacks from renderTag.
 * Custom BbCode can notably use renderSubTree to render children BbCodes and other content such as emojis, smiley, markdown, I guess.
 * 
 * Based on \XF\BbCode\Renderer\Html
 * Which derives from \XF\BbCode\Renderer\AbstractRenderer
 * Which derives from \XF\BbCode\Traverser
 */
class Html extends XFCP_Html
{

	public function addDefaultTags()
	{
		// This is an override so we need to call the parent function to preserve stock behaviour
		parent::addDefaultTags();
		
		// Add our new BbCode tags
		// Some reason that's not working?
		//$this->addTag('h1', ['replace' => ['<h1>', '</h1>']]);
		//$this->addTag('h2', ['replace' => ['<h2>', '</h2>']]);
		//$this->addTag('h3', ['replace' => ['<h3>', '</h3>']]);
		//$this->addTag('h4', ['replace' => ['<h4>', '</h4>']]);
		//$this->addTag('h5', ['replace' => ['<h5>', '</h5>']]);
		//$this->addTag('h6', ['replace' => ['<h6>', '</h6>']]);
		//$this->addTag('doo', ['replace' => ['<h1>', '</h1>']]);

	}

	/**
	 * Inject our rendered TOC and reset it.
	 */
	public function filterFinalOutput($output)
	{
		//\XF::logError("Html::filterFinalOutput");

		// $tocId = 0;
		// $min = 0;
		// $max = 8;

		// Look-up our special TOC marker and extract id, min and max depth
		$output = preg_replace_callback('~\[TOC-(\d+)-(\d+)-(\d+)\]~i', function ($matches) 
		{
            $render = \Slions\Toc\BbCode::getToc($matches[1])->renderHtmlToc($matches[2],$matches[3]);
			//
			\Slions\Toc\BbCode::resetToc($matches[1]);
			return $render;
        }
		, $output);

		// Added that for BB code help page to render properly
		// See: https://staging.slions.net/help/bb-codes/
		\Slions\Toc\BbCode::resetToc(0);

		//{
			// $tocId = $res[1];
			// $min = $res[2];
			// $max = $res[3];
			
			// $tocRender = Slions\Toc\BbCode::getToc($tocId)->renderHtmlToc($min,$max);
		//}
		

		// TODO: render our TOC

		return parent::filterFinalOutput($output);
	}

}



<?php

namespace Slions\Toc\XF\BbCode\Renderer;

#use XF\BbCode\Traverser;
#use XF\Str\Formatter;
#use XF\Template\Templater;
#use XF\Util\Arr;

class EditorHtml extends XFCP_EditorHtml
{

	public function addDefaultTags()
	{
		// This is an override so we need to call the parent function to preserve stock behaviour
		parent::addDefaultTags();
		
		//\XF::dump("addDefaultTags");

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

	public function getCustomTagConfig(array $tag)
	{
		//\XF::dump($tag);

		if ($tag['callback_class']!='Slions\Toc\BbCode')
		{
			// Do not enable tags that do not belong to us
			return parent::getCustomTagConfig($tag);
		}
				
		// Following code was taken from XF\BbCode\Renderer\Html.php
		$output = [];

		if ($tag['bb_code_mode'] == 'replace')
		{
			$output['replace'] = $tag['replace_html'];
		}
		else if ($tag['bb_code_mode'] == 'callback')
		{
			$output['callback'] = [$tag['callback_class'], $tag['callback_method']];
		}

		if ($tag['trim_lines_after'])
		{
			$output['trimAfter'] = $tag['trim_lines_after'];
		}

		if ($tag['disable_nl2br'])
		{
			$output['stopBreakConversion'] = true;
		}

		if ($tag['allow_empty'])
		{
			$output['keepEmpty'] = true;
		}

		return $output;
	}


}



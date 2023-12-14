<?php

namespace Slions\Toc\XF\BbCode\Renderer;

#use XF\BbCode\Traverser;
#use XF\Str\Formatter;
#use XF\Template\Templater;
#use XF\Util\Arr;

class Html extends XFCP_Html
{
	// We use those to be able to render our heading inside our TOC
	public \XF\BbCode\Parser $parser;
	public \XF\BbCode\RuleSet $ruleSet;


	public function render($string, \XF\BbCode\Parser $parser, \XF\BbCode\RuleSet $rules, array $options = [])
	{
		// Provide access to parser and rules so that our TOC headings can be rendered too
		$this->parser = $parser;
		$this->ruleSet = $rules;
		
		// Just call parent implementation now
		return parent::render($string, $parser, $rules, $options);
	}



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

}



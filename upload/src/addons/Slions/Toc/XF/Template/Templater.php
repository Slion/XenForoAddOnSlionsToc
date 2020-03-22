<?php

namespace Slions\Toc\XF\Template;

#use XF\BbCode\Traverser;
#use XF\Str\Formatter;
#use XF\Template\Templater;
#use XF\Util\Arr;

class Templater extends XFCP_Templater
{

	public function getCurrentTemplateName()
	{
		return $this->currentTemplateName;
	}

	public function getRouter()
	{
		return parent::getRouter();
	}

}



<?php

namespace Slions\Toc\XF\BbCode\Renderer;

/**
 * This renderer is notably used when saving after editing a post.
 * Not sure why though.
 * 
 * Based on \XF\BbCode\Renderer\SimpleHtml
 * Which derives from \XF\BbCode\Renderer\Html
 * Which derives from \XF\BbCode\Renderer\AbstractRenderer
 * Which derives from \XF\BbCode\Traverser
 */
class SimpleHtml extends XFCP_SimpleHtml
{

	/**
	 * Needed to reset our TOC after saving a post without actually changing it to avoid having duplicate TOC content.
     * Related to: https://github.com/Slion/XenForoAddOnSlionsToc/issues/7
     * See: https://xenforo.com/community/threads/when-is-the-simplehtml-renderer-used.218309/
	 */
	public function filterFinalOutput($output)
	{
		//\XF::logError("SimpleHtml::filterFinalOutput");

        $output = preg_replace_callback('~\[TOC-(\d+)-(\d+)-(\d+)\]~i', function ($matches) 
		{
            $render = \Slions\Toc\BbCode::getToc($matches[1])->renderHtmlToc($matches[2],$matches[3]);
			//
			\Slions\Toc\BbCode::resetToc($matches[1]);
			return $render;
        }
		, $output);

		return parent::filterFinalOutput($output);
	}

}



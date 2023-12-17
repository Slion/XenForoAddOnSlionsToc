<?php

namespace Slions\Toc\XF\BbCode;

#use XF\BbCode\Traverser;
#use XF\Str\Formatter;
#use XF\Template\Templater;
#use XF\Util\Arr;

/**
 * Not actually used.
 * Not sure why it did not work when we defined our parser here instead than in the Html renderer.
 */
class RuleSet extends XFCP_RuleSet
{

    /**
     * Needed to support option keys.
     * See: https://xenforo.com/community/threads/font-awesome-5-editor-button-management-markdown-support-and-more.154701/#post-1287382
     */
    public function getCustomTagConfig(array $tag)
	{
        // Get default config 
        $output = parent::getCustomTagConfig($tag);

		// Check if that tag belongs to us
		if (($tag['callback_class']=='Slions\Toc\BbCode'))
		// Only render Hx tags in HTML editor, TOC tag is left raw for now
			//&& $tag['callback_method']=='handleTagH'))
		{
			$output['supportOptionKeys'] = RuleSet::OPTION_KEYS_BOTH;
		}

        return $output;
	
	}

}



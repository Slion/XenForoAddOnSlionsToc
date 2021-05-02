<?php

namespace Slions\Toc\XF\BbCode\Renderer;

#use XF\BbCode\Traverser;
#use XF\Str\Formatter;
#use XF\Template\Templater;
#use XF\Util\Arr;

/**
 * That guy decides which BbCode tag gets rendered in our HTML WYSIWYG editor.
 */
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

	/**
	 * Enable our custom BB code to render in WYSIWYG editor. 
	 */
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

		//$output['trimAfter'] = 0;
		//$output['stopBreakConversion'] = true;
		//$output['keepEmpty'] = false;

		return $output;
	}


	/**
	 * From EditorHtml.php
	 */
	/*
	protected function replaceEmptyContent(array $match)
	{
		$emptyParaText = ''; // was  <br /> -- Froala seems to handle this for us and removing this fixes some minor issues

		if (strlen(trim($match[2])) == 0)
		{
			// paragraph is actually empty
			$output = $emptyParaText;
		}
		else
		{
			$test = strip_tags($match[2], '<empty-content><img><br><hr>');
			if (trim($test) == '<empty-content />')
			{
				$output = str_replace('<empty-content />', $emptyParaText, $match[2]);
			}
			else
			{
				// we had a break
				$output = str_replace('<empty-content />', '', $match[2]);
			}
		}

		return $match[1] . $output . $match[3];
	}
	*/

	/**
	 * From Html.php
	 */
	/*
	public function renderString($string, array $options)
	{
		if ($this->trimAfter)
		{			
			$string = preg_replace('#^([ \t]*\r?\n){1,' . $this->trimAfter . '}#i', '', $string);
			if (!empty($string))
			{
				//$string = '-'. $string . '-';		
			}
			//$string = "trim after: " . $this->trimAfter . $string;
			$this->trimAfter = 0;
		}

		return $this->filterString($string, $options);
	}
	*/

	/**
	 * From EditorHtml.php
	 */
	/*
	public function filterString($string, array $options)
	{		
		if (!empty($options['treatAsStructuredText']))
		{
			$string = $this->formatter->convertStructuredTextLinkToBbCode($string);
			$string = $this->formatter->convertStructuredTextMentionsToBbCode($string);
		}

		if (empty($options['stopSmilies']))
		{
			$string = $this->formatter->replaceSmiliesHtml($string);
			$string = $this->formatter->getEmojiFormatter()->formatEmojiToImage($string);
		}
		else
		{
			$string = htmlspecialchars($string);
		}

		$string = str_replace("\t", '    ', $string);

		// doing this twice handles situations with 3 spaces
		$string = str_replace('  ', '&nbsp; ', $string);
		$string = str_replace('  ', '&nbsp; ', $string);

		//return $string;

		if (empty($options['stopBreakConversion']))
		{
			if (!empty($options['inList']) || !empty($options['inTable']))
			{
				$string = nl2br($string);
			}
			else
			{
				//$string = preg_replace('/(\r\n|\n|\r)$/', "", $string);
				$string = preg_replace('/\r\n|\n|\r/', "<break />\n", $string);
				//$string = preg_replace('/$/', "\n", $string);
			}
		}

		return $string;
	}
	*/

	/**
	 * In our working case with HEADING this is taking care of removing our <break />
	 * From EditorHtml.php
	 * 
	 * We had to override and copy this function implementation in order to fix our extra new line whenever 
	 */
	public function filterFinalOutput($output)
	{
		//return $output;
		$debug = false;

		$btOpen = $this->blockTagsOpenRegex;
		$btClose = $this->blockTagsCloseRegex;

		//protected $blockTagsOpenRegex = '<p|<div|<blockquote|<ul|<ol|<table|<h\\d|<hr';
		//protected $blockTagsCloseRegex = '</p>|</div>|</blockquote>|</ul>|</ol>|</table>|</h\\d>|</hr>';
	

		$debugNl = ($debug ? "\n" : '');

		if ($debug) { echo '<hr /><b>Original:</b><br />'. nl2br(htmlspecialchars($output)); }

		$output = $this->cleanUpInlineListWrapping($output);

		$output = preg_replace('#\s*<break-start />(?>\s*)(?!' . $btOpen . '|' . $btClose . '|<break-start|$)#i', $debugNl . "<p>", $output);
		$output = preg_replace('#\s*<break-start />#i', '', $output);
		$output = preg_replace('#(' . $btClose . ')\s*<break />#i', "\\1", $output);
		// Original: creates an extra <p><br /></p>
		//$output = preg_replace('#<break />\s*(' . $btOpen . '|' . $btClose . ')#i', "</p>" . ($debug ? "\n" : '') . "\\1", $output);
		// Fixed:
		$output = preg_replace('#<break />\s*(' . $btOpen . '|' . $btClose . ')#i', $debugNl . "\\1", $output);

		// Original, causes one extra <p><br /></p> if we have at least one empty line between text and next header
		//$output = preg_replace('#<break />\s*#i', "</p>" . $debugNl . "<p>", $output);
		// Fix:
		$output = preg_replace('#<break />\s*#i', $debugNl . "<p />", $output);

		if ($debug) { echo '<hr /><b>Post-break:</b><br />'. nl2br(htmlspecialchars($output)); }

		$output = trim($output);
		if (!preg_match('#^(' . $btOpen . ')#i', $output))
		{
			$output = '<p>' . $output;
		}
		if (!preg_match('#(' . $btClose . ')$#i', $output))
		{
			$output .= '</p>';
		}
		else if (preg_match('#(</blockquote>|</table>|</ol>|</ul>)$#i', $output))
		{
			$output .= $debugNl . '<p></p>';
		}

		$output = preg_replace_callback('#(<p[^>]*>)(.*)(</p>)#siU',
			[$this, 'replaceEmptyContent'], $output
		);
		$output = str_replace('<empty-content />', '', $output); // just in case

		$output = $this->fixListStyles($output);

		if ($debug) { echo '<hr /><b>Final:</b><br />'. nl2br(htmlspecialchars($output)); }

		return $output;
	}

	/**
	 * From HtmlEditor.php
	 */
	/*
	public function wrapHtml($open, $inner, $close, $option = null)
	{
		//return $open . $inner . $close;

		
		if ($option !== null)
		{
			$open = sprintf($open, $option);
			$close = sprintf($close, $option);
		}

		$btOpen = $this->blockTagsOpenRegex;
		$btClose = $this->blockTagsCloseRegex;

		$inner = preg_replace('#(<break />\s*)(?=<break />|$)#i', '\\1<empty-content />', $inner);

		if (preg_match('#^(' . $btOpen . ')#i', $open))
		{
			$inner = preg_replace(
				'#<break-start />(?>\s*)(?!' . $btOpen . '|' . $btClose . '|$)#i',
				"$close\\0$open",
				$inner
			);
			$inner = preg_replace(
				'#<break />(?>\s*)(?!' . $btOpen . ')#i',
				"$close\\0$open",
				$inner
			);
		}
		else
		{
			if (preg_match('#^<break />#i', $inner))
			{
				$inner = '<empty-content />' . $inner;
			}
			$inner = preg_replace('#<break />\s*((' . $btOpen . ')[^>]*>)?#i', "$close\\0$open", $inner);
		}
			
		return $open . $inner . $close;
	}
	*/

}



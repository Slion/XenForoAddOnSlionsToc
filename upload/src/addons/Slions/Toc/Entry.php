<?php

namespace Slions\Toc;

/**
 * Define an entry in our TOC.
 */
class Entry
{
	public $mDepth = 0;
	public $mText = "Root";
	public $mId = "";
	// Index of this entry within the current post.
	public $mIndex = -1;
	public $mChildren = array();
	public $mUsed = false;
	// Only used by our root entry to help build our headers
	public $mNextHeaderId = 0;
	public $mLastEditDate = 0;
	
	/**
	 * For debug purposes.
	 */
	public function renderText($aDepth, $aMaxDepth)
	{
		if ($aDepth>$aMaxDepth)
		{
			return "";
		}
		
		// Setup our prefix according to our depth
		$count=$aDepth;
		$prefix = "";
		while ($count>0)
		{
			$prefix .= "----";
			$count--;
		}			
		
		$output = $prefix . $this->mDepth . ':' . $this->mId . ':' . $this->mText . '<br />';
		foreach($this->mChildren as $child)
		{
			$output .= $child->renderText($aDepth+1,$aMaxDepth);
		}
		
		return $output;
	}
	
	/**
	 * Render our TOC as HTML
	 */
	public function renderHtmlToc($aDepth, $aMaxDepth)
	{
		// Provide surrounding div
		$output = '<div class="postToc">';
		$output .= '<div class="postTocInner">';
		// and go recursive
		$output .= $this->renderHtml($aDepth, $aMaxDepth);
		$output .= '</div></div>';
		
		return $output;
	}
	
	/**
	 * Do proper HTML render.	
	 */
	private function renderHtml($aDepth, $aMaxDepth)
	{
		if ($aDepth>$aMaxDepth)
		{
			return "";
		}
		
		
		$output = "";
		if ($this->mDepth>0) // Skip the root node
		{
			/*
			$render = $this->mText;
			
			// For some reason our Html renderer class extension is not visible here 
			// Or is it that that property was just not set
			// That was happening when saving after edition. The renderer was of type SimpleHtml.			
			if (property_exists($this->renderer,'parser'))
			{
				// We need to render our heading text first so that nested BB and emojis are applied properly 
				$render = $this->renderer->render($this->mText,$this->renderer->parser,$this->renderer->ruleSet,$this->options);
			}
			else
			{
				// We get there when coming back from the editor without having changed anything
				//$render = "Not good";
			}
			*/
			
			//$output .= '<li><a href="'. $GLOBALS['requestUri'] .'#'. $this->mId . '">' . $this->mText . '</a></li>';
			$output .= '<li><a href="#'. $this->mId . '">' . $this->mText . '</a></li>';
		}
		
		// Output our children if any
		if (count($this->mChildren)>0)
		{
			$output .= '<ul>';
			foreach($this->mChildren as $child)
			{				
				$output .= $child->renderHtml($aDepth+1,$aMaxDepth);
			}
			$output .= '</ul>';			
		}
		return $output;
	}

	
	/**
	 * Add an entry to our TOC.
	 * Return false if the given entry does not belong in this branch of our TOC.
	 * Return true if the given entry was added to this branch of our TOC.
	 */
	public function addTocEntry($aNewTocEntry)
	{	
		/*
		if ($this->mDepth == 0) 
		{
			if ($this->idExists($aNewTocEntry->mId))
			{
				return false;
			}
		}*/

		// Check if it's potentially one of our children
		if ($aNewTocEntry->mDepth > $this->mDepth)
		{
			if (count($this->mChildren)==0)
			{
				// No child yet take that one then
				array_push($this->mChildren, $aNewTocEntry);
				return true;
			}
			
			if ((end($this->mChildren)->addTocEntry($aNewTocEntry))==false)
			{
				// It does not fit in the last of our children so we will keep it here
				// That's the use case where we have non linear header levels.
				array_push($this->mChildren, $aNewTocEntry);
				return true;										
			}
			else
			{
				// It was kept by one of our children, make sure nobody else will use it then
				return true;
			}
		}
				
		return false;
	}

	/**
	 * Recursively count our children.
	 */
	public function countEntries()
	{
		$count = count($this->mChildren);

		foreach($this->mChildren as $child)
		{
			$count += $child->countEntries();
		}

		return $count;
	}

	/**
	 *
	 */
	public function idExists($aId)
	{
		//$count = count($this->mChildren);

		foreach($this->mChildren as $child)
		{
			if ($child->mId == $aId) 
			{
				return true;
			}

			return $child->idExists($aId);
		}

		return false;
	}

	/**
	 * Check if all our headers are accounted for.
	 */
	public function isComplete()
	{
		return $this->countEntries()==$this->mNextHeaderId;
	}

}

<?php
/**
*
* @package phpbb_revisions
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* An API class to interface between the FineDiff diff engine and phpBB
*
* @package phpbb_revisions
*/
class phpbb_revisions_diff_engine_finediff extends phpbb_revisions_diff_engine_base
{
	/**
	* Set the diff granularity
	*
	* @param string $granularity How fine-grained is the diff (i.e. character|word|line)
	* @return null
	*/
	public function set_granularity($granularity)
	{
		if (!class_exists('FineDiff'))
		{
			require($this->phpbb_root_path . 'includes/revisions/finediff.' . $this->phpEx);
		}
		switch ($granularity)
		{
			default:
			case 'word':
				$this->granularity = FineDiff::$wordGranularity;
			break;

			case 'character':
				$this->granularity = FineDiff::$characterGranularity;
			break;

			case 'line':
				$this->granularity = FineDiff::$paragraphGranularity;
			break;
		}
	}

	/**
	* Generate/Process a diff (not the output method, just the method that actually determines the diff)
	*
	* 	Note: This is purely a worker method; it should not actually return anything,
	*	but rather put the results in the $this->operations property
	*
	* @return null
	*/
	public function process()
	{
		// the actual diff action is done in the diff object's __construct() so we don't have to do it here.
		// So here we just put the edits into our operations property
		$this->operations = $this->diff->getOps();
	}

	/**
	* Render the diff
	*
	* @return string HTML formatted diff
	*/
	public function render()
	{
		return html_entity_decode(mb_convert_encoding($this->diff->renderDiffToHTML(), 'UTF-8'));
	}

	/**
	* Count the number of additions between the two strings
	*
	* @return int Number of additions
	*/
	public function additions_count()
	{
		$edits = 0;
		foreach ($this->operations AS $edit)
		{
			if ($edit instanceof FineDiffInsertOp)
			{
				$edits++;
			}
		}
		return $edits;
	}

	/**
	* Count the number of deletions between the two strings
	*
	* @return int Number of deletions
	*/
	public function deletions_count()
	{
		$edits = 0;
		foreach ($this->operations AS $edit)
		{
			if ($edit instanceof FineDiffDeleteOp)
			{
				$edits++;
			}
		}
		return $edits;
	}

	/**
	* Count the number of total edits/operations between the two strings
	*
	* @return int Number of edits
	*/
	public function operations_count()
	{
		return sizeof($this->operations);
	}

	/**
	* Sets the diff object to the $diff property
	*
	* @return null
	*/
	protected function get_diff_obj()
	{
		if (!class_exists('FineDiff'))
		{
			require($this->phpbb_root_path . 'includes/revisions/finediff.' . $this->phpEx);
		}
		$this->diff = new FineDiff($this->from, $this->to, $this->granularity);
	}
}

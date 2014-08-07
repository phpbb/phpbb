<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
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
* Code from pear.php.net, Text_Diff-1.1.0 package
* http://pear.php.net/package/Text_Diff/
*
* Modified by phpBB Limited to meet our coding standards
* and being able to integrate into phpBB
*
* General API for generating and formatting diffs - the differences between
* two sequences of strings.
*
* Copyright 2004 Geoffrey T. Dairiki <dairiki@dairiki.org>
* Copyright 2004-2008 The Horde Project (http://www.horde.org/)
*
* @package diff
* @author  Geoffrey T. Dairiki <dairiki@dairiki.org>
*/
class diff
{
	/**
	* Array of changes.
	* @var array
	*/
	var $_edits;

	/**
	* Computes diffs between sequences of strings.
	*
	* @param array	&$from_content	An array of strings. Typically these are lines from a file.
	* @param array	&$to_content	An array of strings.
	* @param bool	$preserve_cr	If true, \r is replaced by a new line in the diff output
	*/
	function diff(&$from_content, &$to_content, $preserve_cr = true)
	{
		$diff_engine = new diff_engine();
		$this->_edits = $diff_engine->diff($from_content, $to_content, $preserve_cr);
	}

	/**
	* Returns the array of differences.
	*/
	function get_diff()
	{
		return $this->_edits;
	}

	/**
	* returns the number of new (added) lines in a given diff.
	*
	* @since Text_Diff 1.1.0
	*
	* @return integer The number of new lines
	*/
	function count_added_lines()
	{
		$count = 0;

		for ($i = 0, $size = sizeof($this->_edits); $i < $size; $i++)
		{
			$edit = $this->_edits[$i];

			if (is_a($edit, 'diff_op_add') || is_a($edit, 'diff_op_change'))
			{
				$count += $edit->nfinal();
			}
		}
		return $count;
	}

	/**
	* Returns the number of deleted (removed) lines in a given diff.
	*
	* @since Text_Diff 1.1.0
	*
	* @return integer The number of deleted lines
	*/
	function count_deleted_lines()
	{
		$count = 0;

		for ($i = 0, $size = sizeof($this->_edits); $i < $size; $i++)
		{
			$edit = $this->_edits[$i];

			if (is_a($edit, 'diff_op_delete') || is_a($edit, 'diff_op_change'))
			{
				$count += $edit->norig();
			}
		}
		return $count;
	}

	/**
	* Computes a reversed diff.
	*
	* Example:
	* <code>
	* $diff = new diff($lines1, $lines2);
	* $rev = $diff->reverse();
	* </code>
	*
	* @return diff  A Diff object representing the inverse of the original diff.
	*               Note that we purposely don't return a reference here, since
	*               this essentially is a clone() method.
	*/
	function reverse()
	{
		if (version_compare(zend_version(), '2', '>'))
		{
			$rev = clone($this);
		}
		else
		{
			$rev = $this;
		}

		$rev->_edits = array();

		for ($i = 0, $size = sizeof($this->_edits); $i < $size; $i++)
		{
			$edit = $this->_edits[$i];
			$rev->_edits[] = $edit->reverse();
		}

		return $rev;
	}

	/**
	* Checks for an empty diff.
	*
	* @return boolean  True if two sequences were identical.
	*/
	function is_empty()
	{
		for ($i = 0, $size = sizeof($this->_edits); $i < $size; $i++)
		{
			$edit = $this->_edits[$i];

			// skip diff_op_copy
			if (is_a($edit, 'diff_op_copy'))
			{
				continue;
			}

			if (is_a($edit, 'diff_op_delete') || is_a($edit, 'diff_op_add'))
			{
				$orig = $edit->orig;
				$final = $edit->final;

				// We can simplify one case where the array is usually supposed to be empty...
				if (sizeof($orig) == 1 && trim($orig[0]) === '') $orig = array();
				if (sizeof($final) == 1 && trim($final[0]) === '') $final = array();

				if (!$orig && !$final)
				{
					continue;
				}

				return false;
			}

			return false;
		}

		return true;
	}

	/**
	* Computes the length of the Longest Common Subsequence (LCS).
	*
	* This is mostly for diagnostic purposes.
	*
	* @return integer  The length of the LCS.
	*/
	function lcs()
	{
		$lcs = 0;

		for ($i = 0, $size = sizeof($this->_edits); $i < $size; $i++)
		{
			$edit = $this->_edits[$i];

			if (is_a($edit, 'diff_op_copy'))
			{
				$lcs += sizeof($edit->orig);
			}
		}
		return $lcs;
	}

	/**
	* Gets the original set of lines.
	*
	* This reconstructs the $from_lines parameter passed to the constructor.
	*
	* @return array  The original sequence of strings.
	*/
	function get_original()
	{
		$lines = array();

		for ($i = 0, $size = sizeof($this->_edits); $i < $size; $i++)
		{
			$edit = $this->_edits[$i];

			if ($edit->orig)
			{
				array_splice($lines, sizeof($lines), 0, $edit->orig);
			}
		}
		return $lines;
	}

	/**
	* Gets the final set of lines.
	*
	* This reconstructs the $to_lines parameter passed to the constructor.
	*
	* @return array  The sequence of strings.
	*/
	function get_final()
	{
		$lines = array();

		for ($i = 0, $size = sizeof($this->_edits); $i < $size; $i++)
		{
			$edit = $this->_edits[$i];

			if ($edit->final)
			{
				array_splice($lines, sizeof($lines), 0, $edit->final);
			}
		}
		return $lines;
	}

	/**
	* Removes trailing newlines from a line of text. This is meant to be used with array_walk().
	*
	* @param string &$line  The line to trim.
	* @param integer $key  The index of the line in the array. Not used.
	*/
	function trim_newlines(&$line, $key)
	{
		$line = str_replace(array("\n", "\r"), '', $line);
	}

	/**
	* Checks a diff for validity.
	*
	* This is here only for debugging purposes.
	*/
	function _check($from_lines, $to_lines)
	{
		if (serialize($from_lines) != serialize($this->get_original()))
		{
			trigger_error("[diff] Reconstructed original doesn't match", E_USER_ERROR);
		}

		if (serialize($to_lines) != serialize($this->get_final()))
		{
			trigger_error("[diff] Reconstructed final doesn't match", E_USER_ERROR);
		}

		$rev = $this->reverse();

		if (serialize($to_lines) != serialize($rev->get_original()))
		{
			trigger_error("[diff] Reversed original doesn't match", E_USER_ERROR);
		}

		if (serialize($from_lines) != serialize($rev->get_final()))
		{
			trigger_error("[diff] Reversed final doesn't match", E_USER_ERROR);
		}

		$prevtype = null;

		for ($i = 0, $size = sizeof($this->_edits); $i < $size; $i++)
		{
			$edit = $this->_edits[$i];

			if ($prevtype == get_class($edit))
			{
				trigger_error("[diff] Edit sequence is non-optimal", E_USER_ERROR);
			}
			$prevtype = get_class($edit);
		}

		return true;
	}
}

/**
* @package diff
* @author  Geoffrey T. Dairiki <dairiki@dairiki.org>
*/
class mapped_diff extends diff
{
	/**
	* Computes a diff between sequences of strings.
	*
	* This can be used to compute things like case-insensitve diffs, or diffs
	* which ignore changes in white-space.
	*
	* @param array $from_lines         An array of strings.
	* @param array $to_lines           An array of strings.
	* @param array $mapped_from_lines  This array should have the same size number of elements as $from_lines.
	*                                  The elements in $mapped_from_lines and $mapped_to_lines are what is actually
	*                                  compared when computing the diff.
	* @param array $mapped_to_lines    This array should have the same number of elements as $to_lines.
	*/
	function mapped_diff(&$from_lines, &$to_lines, &$mapped_from_lines, &$mapped_to_lines)
	{
		if (sizeof($from_lines) != sizeof($mapped_from_lines) || sizeof($to_lines) != sizeof($mapped_to_lines))
		{
			return false;
		}

		parent::diff($mapped_from_lines, $mapped_to_lines);

		$xi = $yi = 0;
		for ($i = 0; $i < sizeof($this->_edits); $i++)
		{
			$orig = &$this->_edits[$i]->orig;
			if (is_array($orig))
			{
				$orig = array_slice($from_lines, $xi, sizeof($orig));
				$xi += sizeof($orig);
			}

			$final = &$this->_edits[$i]->final;
			if (is_array($final))
			{
				$final = array_slice($to_lines, $yi, sizeof($final));
				$yi += sizeof($final);
			}
		}
	}
}

/**
* @package diff
* @author  Geoffrey T. Dairiki <dairiki@dairiki.org>
*
* @access private
*/
class diff_op
{
	var $orig;
	var $final;

	function &reverse()
	{
		trigger_error('[diff] Abstract method', E_USER_ERROR);
	}

	function norig()
	{
		return ($this->orig) ? sizeof($this->orig) : 0;
	}

	function nfinal()
	{
		return ($this->final) ? sizeof($this->final) : 0;
	}
}

/**
* @package diff
* @author  Geoffrey T. Dairiki <dairiki@dairiki.org>
*
* @access private
*/
class diff_op_copy extends diff_op
{
	function diff_op_copy($orig, $final = false)
	{
		if (!is_array($final))
		{
			$final = $orig;
		}
		$this->orig = $orig;
		$this->final = $final;
	}

	function &reverse()
	{
		$reverse = new diff_op_copy($this->final, $this->orig);
		return $reverse;
	}
}

/**
* @package diff
* @author  Geoffrey T. Dairiki <dairiki@dairiki.org>
*
* @access private
*/
class diff_op_delete extends diff_op
{
	function diff_op_delete($lines)
	{
		$this->orig = $lines;
		$this->final = false;
	}

	function &reverse()
	{
		$reverse = new diff_op_add($this->orig);
		return $reverse;
	}
}

/**
* @package diff
* @author  Geoffrey T. Dairiki <dairiki@dairiki.org>
*
* @access private
*/
class diff_op_add extends diff_op
{
	function diff_op_add($lines)
	{
		$this->final = $lines;
		$this->orig = false;
	}

	function &reverse()
	{
		$reverse = new diff_op_delete($this->final);
		return $reverse;
	}
}

/**
* @package diff
* @author  Geoffrey T. Dairiki <dairiki@dairiki.org>
*
* @access private
*/
class diff_op_change extends diff_op
{
	function diff_op_change($orig, $final)
	{
		$this->orig = $orig;
		$this->final = $final;
	}

	function &reverse()
	{
		$reverse = new diff_op_change($this->final, $this->orig);
		return $reverse;
	}
}


/**
* A class for computing three way diffs.
*
* @package diff
* @author  Geoffrey T. Dairiki <dairiki@dairiki.org>
*/
class diff3 extends diff
{
	/**
	* Conflict counter.
	* @var integer
	*/
	var $_conflicting_blocks = 0;

	/**
	* Computes diff between 3 sequences of strings.
	*
	* @param array &$orig		The original lines to use.
	* @param array &$final1		The first version to compare to.
	* @param array &$final2		The second version to compare to.
	* @param bool $preserve_cr	If true, \r\n and bare \r are replaced by a new line
	*							in the diff output
	*/
	function diff3(&$orig, &$final1, &$final2, $preserve_cr = true)
	{
		$diff_engine = new diff_engine();

		$diff_1 = $diff_engine->diff($orig, $final1, $preserve_cr);
		$diff_2 = $diff_engine->diff($orig, $final2, $preserve_cr);

		unset($diff_engine);

		$this->_edits = $this->_diff3($diff_1, $diff_2);
	}

	/**
	* Return number of conflicts
	*/
	function get_num_conflicts()
	{
		$conflicts = 0;

		for ($i = 0, $size = sizeof($this->_edits); $i < $size; $i++)
		{
			$edit = $this->_edits[$i];

			if ($edit->is_conflict())
			{
				$conflicts++;
			}
		}

		return $conflicts;
	}

	/**
	* Get conflicts content for download. This is generally a merged file, but preserving conflicts and adding explanations to it.
	* A user could then go through this file, search for the conflicts and changes the code accordingly.
	*
	* @param string $label1 the cvs file version/label from the original set of lines
	* @param string $label2 the cvs file version/label from the new set of lines
	* @param string $label_sep the explanation between label1 and label2 - more of a helper for the user
	*
	* @return mixed the merged output
	*/
	function get_conflicts_content($label1 = 'CURRENT_FILE', $label2 = 'NEW_FILE', $label_sep = 'DIFF_SEP_EXPLAIN')
	{
		global $user;

		$label1 = (!empty($user->lang[$label1])) ? $user->lang[$label1] : $label1;
		$label2 = (!empty($user->lang[$label2])) ? $user->lang[$label2] : $label2;
		$label_sep = (!empty($user->lang[$label_sep])) ? $user->lang[$label_sep] : $label_sep;

		$lines = array();

		for ($i = 0, $size = sizeof($this->_edits); $i < $size; $i++)
		{
			$edit = $this->_edits[$i];

			if ($edit->is_conflict())
			{
				// Start conflict label
				$label_start	= array('<<<<<<< ' . $label1);
				$label_mid		= array('======= ' . $label_sep);
				$label_end		= array('>>>>>>> ' . $label2);

				$lines = array_merge($lines, $label_start, $edit->final1, $label_mid, $edit->final2, $label_end);
				$this->_conflicting_blocks++;
			}
			else
			{
				$lines = array_merge($lines, $edit->merged());
			}
		}

		return $lines;
	}

	/**
	* Return merged output (used by the renderer)
	*
	* @return mixed the merged output
	*/
	function merged_output()
	{
		return $this->get_conflicts_content();
	}

	/**
	* Merge the output and use the new file code for conflicts
	*/
	function merged_new_output()
	{
		$lines = array();

		for ($i = 0, $size = sizeof($this->_edits); $i < $size; $i++)
		{
			$edit = $this->_edits[$i];

			if ($edit->is_conflict())
			{
				$lines = array_merge($lines, $edit->final2);
			}
			else
			{
				$lines = array_merge($lines, $edit->merged());
			}
		}

		return $lines;
	}

	/**
	* Merge the output and use the original file code for conflicts
	*/
	function merged_orig_output()
	{
		$lines = array();

		for ($i = 0, $size = sizeof($this->_edits); $i < $size; $i++)
		{
			$edit = $this->_edits[$i];

			if ($edit->is_conflict())
			{
				$lines = array_merge($lines, $edit->final1);
			}
			else
			{
				$lines = array_merge($lines, $edit->merged());
			}
		}

		return $lines;
	}

	/**
	* Get conflicting block(s)
	*/
	function get_conflicts()
	{
		$conflicts = array();

		for ($i = 0, $size = sizeof($this->_edits); $i < $size; $i++)
		{
			$edit = $this->_edits[$i];

			if ($edit->is_conflict())
			{
				$conflicts[] = array($edit->final1, $edit->final2);
			}
		}

		return $conflicts;
	}

	/**
	* @access private
	*/
	function _diff3(&$edits1, &$edits2)
	{
		$edits = array();
		$bb = new diff3_block_builder();

		$e1 = current($edits1);
		$e2 = current($edits2);

		while ($e1 || $e2)
		{
			if ($e1 && $e2 && is_a($e1, 'diff_op_copy') && is_a($e2, 'diff_op_copy'))
			{
				// We have copy blocks from both diffs. This is the (only) time we want to emit a diff3 copy block.
				// Flush current diff3 diff block, if any.
				if ($edit = $bb->finish())
				{
					$edits[] = $edit;
				}

				$ncopy = min($e1->norig(), $e2->norig());
				$edits[] = new diff3_op_copy(array_slice($e1->orig, 0, $ncopy));

				if ($e1->norig() > $ncopy)
				{
					array_splice($e1->orig, 0, $ncopy);
					array_splice($e1->final, 0, $ncopy);
				}
				else
				{
					$e1 = next($edits1);
				}

				if ($e2->norig() > $ncopy)
				{
					array_splice($e2->orig, 0, $ncopy);
					array_splice($e2->final, 0, $ncopy);
				}
				else
				{
					$e2 = next($edits2);
				}
			}
			else
			{
				if ($e1 && $e2)
				{
					if ($e1->orig && $e2->orig)
					{
						$norig = min($e1->norig(), $e2->norig());
						$orig = array_splice($e1->orig, 0, $norig);
						array_splice($e2->orig, 0, $norig);
						$bb->input($orig);
					}
					else
					{
						$norig = 0;
					}

					if (is_a($e1, 'diff_op_copy'))
					{
						$bb->out1(array_splice($e1->final, 0, $norig));
					}

					if (is_a($e2, 'diff_op_copy'))
					{
						$bb->out2(array_splice($e2->final, 0, $norig));
					}
				}

				if ($e1 && ! $e1->orig)
				{
					$bb->out1($e1->final);
					$e1 = next($edits1);
				}

				if ($e2 && ! $e2->orig)
				{
					$bb->out2($e2->final);
					$e2 = next($edits2);
				}
			}
		}

		if ($edit = $bb->finish())
		{
			$edits[] = $edit;
		}

		return $edits;
	}
}

/**
* @package diff
* @author  Geoffrey T. Dairiki <dairiki@dairiki.org>
*
* @access private
*/
class diff3_op
{
	function diff3_op($orig = false, $final1 = false, $final2 = false)
	{
		$this->orig = $orig ? $orig : array();
		$this->final1 = $final1 ? $final1 : array();
		$this->final2 = $final2 ? $final2 : array();
	}

	function merged()
	{
		if (!isset($this->_merged))
		{
			// Prepare the arrays before we compare them. ;)
			$this->solve_prepare();

			if ($this->final1 === $this->final2)
			{
				$this->_merged = &$this->final1;
			}
			else if ($this->final1 === $this->orig)
			{
				$this->_merged = &$this->final2;
			}
			else if ($this->final2 === $this->orig)
			{
				$this->_merged = &$this->final1;
			}
			else
			{
				// The following tries to aggressively solve conflicts...
				$this->_merged = false;
				$this->solve_conflict();
			}
		}

		return $this->_merged;
	}

	function is_conflict()
	{
		return ($this->merged() === false) ? true : false;
	}

	/**
	* Function to prepare the arrays for comparing - we want to skip over newline changes
	* @author acydburn
	*/
	function solve_prepare()
	{
		// We can simplify one case where the array is usually supposed to be empty...
		if (sizeof($this->orig) == 1 && trim($this->orig[0]) === '') $this->orig = array();
		if (sizeof($this->final1) == 1 && trim($this->final1[0]) === '') $this->final1 = array();
		if (sizeof($this->final2) == 1 && trim($this->final2[0]) === '') $this->final2 = array();

		// Now we only can have the case where the only difference between arrays are newlines, so compare all cases

		// First, some strings we can compare...
		$orig = $final1 = $final2 = '';

		foreach ($this->orig as $null => $line) $orig .= trim($line);
		foreach ($this->final1 as $null => $line) $final1 .= trim($line);
		foreach ($this->final2 as $null => $line) $final2 .= trim($line);

		// final1 === final2
		if ($final1 === $final2)
		{
			// We preserve the part which will be used in the merge later
			$this->final2 = $this->final1;
		}
		// final1 === orig
		else if ($final1 === $orig)
		{
			// Here it does not really matter what we choose, but we will use the new code
			$this->orig = $this->final1;
		}
		// final2 === orig
		else if ($final2 === $orig)
		{
			// Here it does not really matter too (final1 will be used), but we will use the new code
			$this->orig = $this->final2;
		}
	}

	/**
	* Find code portions from $orig in $final1 and use $final2 as merged instance if provided
	* @author acydburn
	*/
	function _compare_conflict_seq($orig, $final1, $final2 = false)
	{
		$result = array('merge_found' => false, 'merge' => array());

		$_orig = &$this->$orig;
		$_final1 = &$this->$final1;

		// Ok, we basically search for $orig in $final1
		$compare_seq = sizeof($_orig);

		// Go through the conflict code
		for ($i = 0, $j = 0, $size = sizeof($_final1); $i < $size; $i++, $j = $i)
		{
			$line = $_final1[$i];
			$skip = 0;

			for ($x = 0; $x < $compare_seq; $x++)
			{
				// Try to skip all matching lines
				if (trim($line) === trim($_orig[$x]))
				{
					$line = (++$j < $size) ? $_final1[$j] : $line;
					$skip++;
				}
			}

			if ($skip === $compare_seq)
			{
				$result['merge_found'] = true;

				if ($final2 !== false)
				{
					$result['merge'] = array_merge($result['merge'], $this->$final2);
				}
				$i += ($skip - 1);
			}
			else if ($final2 !== false)
			{
				$result['merge'][] = $line;
			}
		}

		return $result;
	}

	/**
	* Tries to solve conflicts aggressively based on typical "assumptions"
	* @author acydburn
	*/
	function solve_conflict()
	{
		$this->_merged = false;

		// CASE ONE: orig changed into final2, but modified/unknown code in final1.
		// IF orig is found "as is" in final1 we replace the code directly in final1 and populate this as final2/merge
		if (sizeof($this->orig) && sizeof($this->final2))
		{
			$result = $this->_compare_conflict_seq('orig', 'final1', 'final2');

			if ($result['merge_found'])
			{
				$this->final2 = $result['merge'];
				$this->_merged = &$this->final2;
				return;
			}

			$result = $this->_compare_conflict_seq('final2', 'final1');

			if ($result['merge_found'])
			{
				$this->_merged = &$this->final1;
				return;
			}

			// Try to solve $Id$ issues. ;)
			if (sizeof($this->orig) == 1 && sizeof($this->final1) == 1 && sizeof($this->final2) == 1)
			{
				$match = '#^' . preg_quote('* @version $Id: ', '#') . '[a-z\._\- ]+[0-9]+ [0-9]{4}-[0-9]{2}-[0-9]{2} [0-9\:Z]+ [a-z0-9_\- ]+\$$#';

				if (preg_match($match, $this->orig[0]) && preg_match($match, $this->final1[0]) && preg_match($match, $this->final2[0]))
				{
					$this->_merged = &$this->final2;
					return;
				}
			}

			$second_run = false;

			// Try to solve issues where the only reason why the above did not work is a newline being removed in the final1 code but exist in the orig/final2 code
			if (trim($this->orig[0]) === '' && trim($this->final2[0]) === '')
			{
				unset($this->orig[0], $this->final2[0]);
				$this->orig = array_values($this->orig);
				$this->final2 = array_values($this->final2);

				$second_run = true;
			}

			// The same is true for a line at the end. ;)
			if (sizeof($this->orig) && sizeof($this->final2) && sizeof($this->orig) === sizeof($this->final2) && trim($this->orig[sizeof($this->orig)-1]) === '' && trim($this->final2[sizeof($this->final2)-1]) === '')
			{
				unset($this->orig[sizeof($this->orig)-1], $this->final2[sizeof($this->final2)-1]);
				$this->orig = array_values($this->orig);
				$this->final2 = array_values($this->final2);

				$second_run = true;
			}

			if ($second_run)
			{
				$result = $this->_compare_conflict_seq('orig', 'final1', 'final2');

				if ($result['merge_found'])
				{
					$this->final2 = $result['merge'];
					$this->_merged = &$this->final2;
					return;
				}

				$result = $this->_compare_conflict_seq('final2', 'final1');

				if ($result['merge_found'])
				{
					$this->_merged = &$this->final1;
					return;
				}
			}

			return;
		}

		// CASE TWO: Added lines from orig to final2 but final1 had added lines too. Just merge them.
		if (!sizeof($this->orig) && $this->final1 !== $this->final2 && sizeof($this->final1) && sizeof($this->final2))
		{
			$result = $this->_compare_conflict_seq('final2', 'final1');

			if ($result['merge_found'])
			{
				$this->final2 = $this->final1;
				$this->_merged = &$this->final1;
			}
			else
			{
				$result = $this->_compare_conflict_seq('final1', 'final2');

				if (!$result['merge_found'])
				{
					$this->final2 = array_merge($this->final1, $this->final2);
					$this->_merged = &$this->final2;
				}
				else
				{
					$this->final2 = $this->final1;
					$this->_merged = &$this->final1;
				}
			}

			return;
		}

		// CASE THREE: Removed lines (orig has the to-remove line(s), but final1 has additional lines which does not need to be removed). Just remove orig from final1 and then use final1 as final2/merge
		if (!sizeof($this->final2) && sizeof($this->orig) && sizeof($this->final1) && $this->orig !== $this->final1)
		{
			$result = $this->_compare_conflict_seq('orig', 'final1');

			if (!$result['merge_found'])
			{
				return;
			}

			// First of all, try to find the code in orig in final1. ;)
			$compare_seq = sizeof($this->orig);
			$begin = $end = -1;
			$j = 0;

			for ($i = 0, $size = sizeof($this->final1); $i < $size; $i++)
			{
				$line = $this->final1[$i];

				if (trim($line) === trim($this->orig[$j]))
				{
					// Mark begin
					if ($begin === -1)
					{
						$begin = $i;
					}

					// End is always $i, the last found line
					$end = $i;

					if (isset($this->orig[$j+1]))
					{
						$j++;
					}
				}
			}

			if ($begin !== -1 && $begin + ($compare_seq - 1) == $end)
			{
				foreach ($this->final1 as $i => $line)
				{
					if ($i < $begin || $i > $end)
					{
						$merged[] = $line;
					}
				}

				$this->final2 = $merged;
				$this->_merged = &$this->final2;
			}

			return;
		}

		return;
	}
}

/**
* @package diff
* @author  Geoffrey T. Dairiki <dairiki@dairiki.org>
*
* @access private
*/
class diff3_op_copy extends diff3_op
{
	function diff3_op_copy($lines = false)
	{
		$this->orig = $lines ? $lines : array();
		$this->final1 = &$this->orig;
		$this->final2 = &$this->orig;
	}

	function merged()
	{
		return $this->orig;
	}

	function is_conflict()
	{
		return false;
	}
}

/**
* @package diff
* @author  Geoffrey T. Dairiki <dairiki@dairiki.org>
*
* @access private
*/
class diff3_block_builder
{
	function diff3_block_builder()
	{
		$this->_init();
	}

	function input($lines)
	{
		if ($lines)
		{
			$this->_append($this->orig, $lines);
		}
	}

	function out1($lines)
	{
		if ($lines)
		{
			$this->_append($this->final1, $lines);
		}
	}

	function out2($lines)
	{
		if ($lines)
		{
			$this->_append($this->final2, $lines);
		}
	}

	function is_empty()
	{
		return !$this->orig && !$this->final1 && !$this->final2;
	}

	function finish()
	{
		if ($this->is_empty())
		{
			return false;
		}
		else
		{
			$edit = new diff3_op($this->orig, $this->final1, $this->final2);
			$this->_init();
			return $edit;
		}
	}

	function _init()
	{
		$this->orig = $this->final1 = $this->final2 = array();
	}

	function _append(&$array, $lines)
	{
		array_splice($array, sizeof($array), 0, $lines);
	}
}

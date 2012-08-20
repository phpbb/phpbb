<?php
/**
*
* @package build
* @copyright (c) 2000 Geoffrey T. Dairiki <dairiki@dairiki.org>
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* Class used internally by WikiDiff to actually compute the diffs.
*
* The algorithm used here is mostly lifted from the perl module
* Algorithm::Diff (version 1.06) by Ned Konz, which is available at:
*   http://www.perl.com/CPAN/authors/id/N/NE/NEDKONZ/Algorithm-Diff-1.06.zip
*
* More ideas are taken from:
*   http://www.ics.uci.edu/~eppstein/161/960229.html
*
* Some ideas are (and a bit of code) are from from analyze.c, from GNU
* diffutils-2.7, which can be found at:
*   ftp://gnudist.gnu.org/pub/gnu/diffutils/diffutils-2.7.tar.gz
*
* Finally, some ideas (subdivision by NCHUNKS > 2, and some optimizations)
* are my own.
*/
class _WikiDiffEngine
{
	var $edits;	// List of editing operation to convert XV to YV.
	var $xv = array(), $yv = array();

	function _WikiDiffEngine($from_lines, $to_lines)
	{
		$n_from = sizeof($from_lines);
		$n_to = sizeof($to_lines);

		$endskip = 0;
		// Ignore trailing and leading matching lines.
		while ($n_from > 0 && $n_to > 0)
		{
			if ($from_lines[$n_from - 1] != $to_lines[$n_to - 1])
			{
				break;
			}

			$n_from--;
			$n_to--;
			$endskip++;
		}

		for ($skip = 0; $skip < min($n_from, $n_to); $skip++)
		{
			if ($from_lines[$skip] != $to_lines[$skip])
			{
				break;
			}
		}
		$n_from -= $skip;
		$n_to -= $skip;

		$xlines = array();
		$ylines = array();

		// Ignore lines which do not exist in both files.
		for ($x = 0; $x < $n_from; $x++)
		{
			$xhash[$from_lines[$x + $skip]] = 1;
		}

		for ($y = 0; $y < $n_to; $y++)
		{
			$line = $to_lines[$y + $skip];
			$ylines[] = $line;

			if (($this->ychanged[$y] = empty($xhash[$line])))
			{
				continue;
			}
			$yhash[$line] = 1;
			$this->yv[] = $line;
			$this->yind[] = $y;
		}

		for ($x = 0; $x < $n_from; $x++)
		{
			$line = $from_lines[$x + $skip];
			$xlines[] = $line;

			if (($this->xchanged[$x] = empty($yhash[$line])))
			{
				continue;	// fixme? what happens to yhash/xhash when
							// there are two identical lines??
			}
			$this->xv[] = $line;
			$this->xind[] = $x;
		}

		// Find the LCS.
		$this->_compareseq(0, sizeof($this->xv), 0, sizeof($this->yv));

		// Merge edits when possible
		$this->_shift_boundaries($xlines, $this->xchanged, $this->ychanged);
		$this->_shift_boundaries($ylines, $this->ychanged, $this->xchanged);

		// Compute the edit operations.
		$this->edits = array();

		if ($skip)
		{
			$this->edits[] = $skip;
		}

		$x = 0;
		$y = 0;

		while ($x < $n_from || $y < $n_to)
		{
			// Skip matching "snake".
			$x0 = $x;
			$ncopy = 0;
			while ($x < $n_from && $y < $n_to && !$this->xchanged[$x] && !$this->ychanged[$y])
			{
				++$x;
				++$y;
				++$ncopy;
			}

			if ($x > $x0)
			{
				$this->edits[] = $x - $x0;
			}

			// Find deletes.
			$x0 = $x;
			$ndelete = 0;

			while ($x < $n_from && $this->xchanged[$x])
			{
				++$x;
				++$ndelete;
			}

			if ($x > $x0)
			{
				$this->edits[] = -($x - $x0);
			}

			// Find adds.
			if (isset($this->ychanged[$y]) && $this->ychanged[$y])
			{
				$adds = array();
				while ($y < $n_to && $this->ychanged[$y])
				{
					$adds[] = $ylines[$y++];
				}
				$this->edits[] = $adds;
			}
		}

		if (!empty($endskip))
		{
			$this->edits[] = $endskip;
		}
	}

	/* Divide the Largest Common Subsequence (LCS) of the sequences
	* [XOFF, XLIM) and [YOFF, YLIM) into NCHUNKS approximately equally
	* sized segments.
	*
	* Returns (LCS, PTS).  LCS is the length of the LCS. PTS is an
	* array of NCHUNKS+1 (X, Y) indexes giving the diving points between
	* sub sequences.  The first sub-sequence is contained in [X0, X1),
	* [Y0, Y1), the second in [X1, X2), [Y1, Y2) and so on.  Note
	* that (X0, Y0) == (XOFF, YOFF) and
	* (X[NCHUNKS], Y[NCHUNKS]) == (XLIM, YLIM).
	*
	* This function assumes that the first lines of the specified portions
	* of the two files do not match, and likewise that the last lines do not
	* match.  The caller must trim matching lines from the beginning and end
	* of the portions it is going to specify.
	*/
	function _diag ($xoff, $xlim, $yoff, $ylim, $nchunks)
	{
		$flip = false;

		if ($xlim - $xoff > $ylim - $yoff)
		{
			// Things seems faster (I'm not sure I understand why)
			// when the shortest sequence in X.
			$flip = true;
			list ($xoff, $xlim, $yoff, $ylim) = array( $yoff, $ylim, $xoff, $xlim);
		}

		if ($flip)
		{
			for ($i = $ylim - 1; $i >= $yoff; $i--)
			{
				$ymatches[$this->xv[$i]][] = $i;
			}
		}
		else
		{
			for ($i = $ylim - 1; $i >= $yoff; $i--)
			{
				$ymatches[$this->yv[$i]][] = $i;
			}
		}

		$this->lcs = 0;
		$this->seq[0]= $yoff - 1;
		$this->in_seq = array();
		$ymids[0] = array();

		$numer = $xlim - $xoff + $nchunks - 1;
		$x = $xoff;

		for ($chunk = 0; $chunk < $nchunks; $chunk++)
		{
			if ($chunk > 0)
			{
				for ($i = 0; $i <= $this->lcs; $i++)
				{
					$ymids[$i][$chunk-1] = $this->seq[$i];
				}
			}

			$x1 = $xoff + (int)(($numer + ($xlim-$xoff)*$chunk) / $nchunks);

			for ( ; $x < $x1; $x++)
			{
				$_index = $flip ? $this->yv[$x] : $this->xv[$x];
				$matches = (isset($ymatches[$_index])) ? $ymatches[$_index] : array();

				if (!$matches)
				{
					continue;
				}
				reset($matches);

				while (list ($junk, $y) = each($matches))
				{
					if (!isset($this->in_seq[$y]) || !$this->in_seq[$y])
					{
						$k = $this->_lcs_pos($y);
						//if (!$k) die('assertion "!$k" failed');
						$ymids[$k] = $ymids[$k-1];
						break;
					}
				}

				while (list ($junk, $y) = each($matches))
				{
					if ($y > $this->seq[$k-1])
					{
						//if ($y >= $this->seq[$k]) die('assertion failed');
						// Optimization: this is a common case:
						//  next match is just replacing previous match.
						$this->in_seq[$this->seq[$k]] = false;
						$this->seq[$k] = $y;
						$this->in_seq[$y] = 1;
					}
					else if (!isset($this->in_seq[$y]) || !$this->in_seq[$y])
					{
						$k = $this->_lcs_pos($y);
						//if (!$k) die('assertion "!$k" failed');
						$ymids[$k] = $ymids[$k-1];
					}
				}
			}
		}

		$seps[] = $flip ? array($yoff, $xoff) : array($xoff, $yoff);
		$ymid = $ymids[$this->lcs];

		for ($n = 0; $n < $nchunks - 1; $n++)
		{
			$x1 = $xoff + (int)(($numer + ($xlim - $xoff) * $n) / $nchunks);
			$y1 = $ymid[$n] + 1;
			$seps[] = $flip ? array($y1, $x1) : array($x1, $y1);
		}
		$seps[] = $flip ? array($ylim, $xlim) : array($xlim, $ylim);

		return array($this->lcs, $seps);
	}

	function _lcs_pos ($ypos)
	{
		$end = $this->lcs;
		if ($end == 0 || $ypos > $this->seq[$end])
		{
			$this->seq[++$this->lcs] = $ypos;
			$this->in_seq[$ypos] = 1;
			return $this->lcs;
		}

		$beg = 1;
		while ($beg < $end)
		{
			$mid = (int)(($beg + $end) / 2);

			if ($ypos > $this->seq[$mid])
			{
				$beg = $mid + 1;
			}
			else
			{
				$end = $mid;
			}
		}

		//if ($ypos == $this->seq[$end]) die("assertion failure");

		$this->in_seq[$this->seq[$end]] = false;
		$this->seq[$end] = $ypos;
		$this->in_seq[$ypos] = 1;
		return $end;
	}

	/* Find LCS of two sequences.
	*
	* The results are recorded in the vectors $this->{x,y}changed[], by
	* storing a 1 in the element for each line that is an insertion
	* or deletion (ie. is not in the LCS).
	*
	* The subsequence of file 0 is [XOFF, XLIM) and likewise for file 1.
	*
	* Note that XLIM, YLIM are exclusive bounds.
	* All line numbers are origin-0 and discarded lines are not counted.
	*/
	function _compareseq ($xoff, $xlim, $yoff, $ylim)
	{
		// Slide down the bottom initial diagonal.
		while ($xoff < $xlim && $yoff < $ylim && $this->xv[$xoff] == $this->yv[$yoff])
		{
			++$xoff;
			++$yoff;
		}

		// Slide up the top initial diagonal.
		while ($xlim > $xoff && $ylim > $yoff && $this->xv[$xlim - 1] == $this->yv[$ylim - 1])
		{
			--$xlim;
			--$ylim;
		}

		if ($xoff == $xlim || $yoff == $ylim)
		{
			$lcs = 0;
		}
		else
		{
			// This is ad hoc but seems to work well.
			//$nchunks = sqrt(min($xlim - $xoff, $ylim - $yoff) / 2.5);
			//$nchunks = max(2,min(8,(int)$nchunks));
			$nchunks = min(7, $xlim - $xoff, $ylim - $yoff) + 1;
			list ($lcs, $seps) = $this->_diag($xoff, $xlim, $yoff, $ylim, $nchunks);
		}

		if ($lcs == 0)
		{
			// X and Y sequences have no common subsequence:
			// mark all changed.
			while ($yoff < $ylim)
			{
				$this->ychanged[$this->yind[$yoff++]] = 1;
			}

			while ($xoff < $xlim)
			{
				$this->xchanged[$this->xind[$xoff++]] = 1;
			}
		}
		else
		{
			// Use the partitions to split this problem into subproblems.
			reset($seps);

			$pt1 = $seps[0];

			while ($pt2 = next($seps))
			{
				$this->_compareseq ($pt1[0], $pt2[0], $pt1[1], $pt2[1]);
				$pt1 = $pt2;
			}
		}
	}

	/* Adjust inserts/deletes of identical lines to join changes
	* as much as possible.
	*
	* We do something when a run of changed lines include a
	* line at one end and have an excluded, identical line at the other.
	* We are free to choose which identical line is included.
	* `compareseq' usually chooses the one at the beginning,
	* but usually it is cleaner to consider the following identical line
	* to be the "change".
	*
	* This is extracted verbatim from analyze.c (GNU diffutils-2.7).
	*/
	function _shift_boundaries ($lines, &$changed, $other_changed)
	{
		$i = 0;
		$j = 0;
		$len = sizeof($lines);

		while (1)
		{
			/*
			* Scan forwards to find beginning of another run of changes.
			* Also keep track of the corresponding point in the other file.
			*/

			while ($i < $len && $changed[$i] == 0)
			{
				while ($other_changed[$j++])
				{
					continue;
				}
				$i++;
			}

			if ($i == $len)
			{
				break;
			}

			$start = $i;

			// Find the end of this run of changes.
			while (isset($changed[++$i]))
			{
				continue;
			}

			while (isset($other_changed[$j]) && $other_changed[$j])
			{
				$j++;
			}

			do
			{
				/*
				* Record the length of this run of changes, so that
				* we can later determine whether the run has grown.
				*/
				$runlength = $i - $start;

				/*
				* Move the changed region back, so long as the
				* previous unchanged line matches the last changed one.
				* This merges with previous changed regions.
				*/
				while ($start && $lines[$start - 1] == $lines[$i - 1])
				{
					$changed[--$start] = 1;
					$changed[--$i] = false;

					while ($changed[$start - 1])
					{
						$start--;
					}

					while ($other_changed[--$j])
					{
						continue;
					}
				}

				/*
				* Set CORRESPONDING to the end of the changed run, at the last
				* point where it corresponds to a changed run in the other file.
				* CORRESPONDING == LEN means no such point has been found.
				*/
				$corresponding = empty($other_changed[$j - 1]) ? $len : $i;

				/*
				* Move the changed region forward, so long as the
				* first changed line matches the following unchanged one.
				* This merges with following changed regions.
				* Do this second, so that if there are no merges,
				* the changed region is moved forward as far as possible.
				*/
				while ($i != $len && $lines[$start] == $lines[$i])
				{
					$changed[$start++] = false;
					$changed[$i++] = 1;

					while ($changed[$i])
					{
						$i++;
					}

					while ($other_changed[++$j])
					{
						$corresponding = $i;
					}
				}
			} while ($runlength != $i - $start);

			/*
			* If possible, move the fully-merged run of changes
			* back to a corresponding run in the other file.
			*/
			while ($corresponding < $i)
			{
				$changed[--$start] = 1;
				$changed[--$i] = 0;

				while ($other_changed[--$j])
				{
					continue;
				}
			}
		}
	}
}

/**
* Class representing a diff between two files.
*/
class Diff
{
	var $edits;

	/**
	* Compute diff between files (or deserialize serialized WikiDiff.)
	*/
	function Diff($from_lines = false, $to_lines = false)
	{
		if ($from_lines && $to_lines)
		{
			$compute = new _WikiDiffEngine($from_lines, $to_lines);
			$this->edits = $compute->edits;
		}
		else if ($from_lines)
		{
			// $from_lines is not really from_lines, but rather
			// a serialized Diff.
			$this->edits = unserialize($from_lines);
		}
		else
		{
			$this->edits = array();
		}
	}

	/**
	* Compute reversed Diff.
	*
	* SYNOPSIS:
	*
	*  $diff = new Diff($lines1, $lines2);
	*  $rev = $diff->reverse($lines1);
	*
	*  // reconstruct $lines1 from $lines2:
	*  $out = $rev->apply($lines2);
	*/
	function reverse ($from_lines)
	{
		$x = 0;
		$rev = new Diff;

		for (reset($this->edits); $edit = current($this->edits); next($this->edits))
		{
			if (is_array($edit))
			{ // Was an add, turn it into a delete.
				$nadd = sizeof($edit);
				if ($nadd == 0)
				{
					die("assertion error");
				}
				$edit = -$nadd;
			}
			else if ($edit > 0)
			{
				// Was a copy --- just pass it through.	      }
				$x += $edit;
			}
			else if ($edit < 0)
			{ // Was a delete, turn it into an add.
				$ndelete = -$edit;
				$edit = array();

				while ($ndelete-- > 0)
				{
					$edit[] = $from_lines[$x++];
				}
			}
			else
			{
				die("assertion error");
			}

			$rev->edits[] = $edit;
		}

		return $rev;
	}

	/**
	* Compose (concatenate) Diffs.
	*
	* SYNOPSIS:
	*
	*  $diff1 = new Diff($lines1, $lines2);
	*  $diff2 = new Diff($lines2, $lines3);
	*  $comp = $diff1->compose($diff2);
	*
	*  // reconstruct $lines3 from $lines1:
	*  $out = $comp->apply($lines1);
	*/
	function compose ($that)
	{
		reset($this->edits);
		reset($that->edits);

		$comp = new Diff;
		$left = current($this->edits);
		$right = current($that->edits);

		while ($left || $right)
		{
			if (!is_array($left) && $left < 0)
			{ // Left op is a delete.
				$newop = $left;
				$left = next($this->edits);
			}
			else if (is_array($right))
			{ // Right op is an add.
				$newop = $right;
				$right = next($that->edits);
			}
			else if (!$left || !$right)
			{
				die ("assertion error");
			}
			else if (!is_array($left) && $left > 0)
			{ // Left op is a copy.
				if ($left <= abs($right))
				{
					$newop = $right > 0 ? $left : -$left;
					$right -= $newop;

					if ($right == 0)
					{
						$right = next($that->edits);
					}
					$left = next($this->edits);
				}
				else
				{
					$newop = $right;
					$left -= abs($right);
					$right = next($that->edits);
				}
			}
			else
			{ // Left op is an add.
				if (!is_array($left))
				{
					die('assertion error');
				}
				$nleft = sizeof($left);

				if ($nleft <= abs($right))
				{
					if ($right > 0)
					{ // Right op is copy
						$newop = $left;
						$right -= $nleft;
					}
					else // Right op is delete
					{
						$newop = false;
						$right += $nleft;
					}

					if ($right == 0)
					{
						$right = next($that->edits);
					}
					$left = next($this->edits);
				}
				else
				{
					unset($newop);

					if ($right > 0)
					{
						for ($i = 0; $i < $right; $i++)
						{
							$newop[] = $left[$i];
						}
					}

					$tmp = array();
					for ($i = abs($right); $i < $nleft; $i++)
					{
						$tmp[] = $left[$i];
					}
					$left = $tmp;
					$right = next($that->edits);
				}
			}

			if (!$op)
			{
				$op = $newop;
				continue;
			}

			if (!$newop)
			{
				continue;
			}

			if (is_array($op) && is_array($newop))
			{
				// Both $op and $newop are adds.
				for ($i = 0; $i < sizeof($newop); $i++)
				{
					$op[] = $newop[$i];
				}
			}
			else if (($op > 0 && $newop > 0) || ($op < 0 && $newop < 0))
			{ // $op and $newop are both either deletes or copies.
				$op += $newop;
			}
			else
			{
				$comp->edits[] = $op;
				$op = $newop;
			}
		}

		if ($op)
		{
			$comp->edits[] = $op;
		}

		return $comp;
	}

	/* Debugging only:
	function _dump ()
	{
		echo "<ol>";
		for (reset($this->edits);
		{
			$edit = current($this->edits);
		}
		next($this->edits))
	  {
	    echo "<li>";
	    if ($edit > 0)
		echo "Copy $edit";
	    else if ($edit < 0)
		echo "Delete " . -$edit;
	    else if (is_array($edit))
	      {
		echo "Add " . sizeof($edit) . "<ul>";
		for ($i = 0; $i < sizeof($edit); $i++)
		    echo "<li>" . htmlspecialchars($edit[$i]);
		echo "</ul>";
	      }
	    else
		die("assertion error");
	  }
	echo "</ol>";
      }
  */

	/**
	* Apply a Diff to a set of lines.
	*
	* SYNOPSIS:
	*
	*  $diff = new Diff($lines1, $lines2);
	*
	*  // reconstruct $lines2 from $lines1:
	*  $out = $diff->apply($lines1);
	*/
	function apply ($from_lines)
	{
		$x = 0;
		$xlim = sizeof($from_lines);

		for (reset($this->edits); $edit = current($this->edits); next($this->edits))
		{
			if (is_array($edit))
			{
				reset($edit);
				while (list ($junk, $line) = each($edit))
				{
					$output[] = $line;
				}
			}
			else if ($edit > 0)
			{
				while ($edit--)
				{
					$output[] = $from_lines[$x++];
				}
			}
			else
			{
				$x += -$edit;
			}
		}

		if ($x != $xlim)
		{
			die(sprintf("Diff::apply: line count mismatch: %s != %s", $x, $xlim));
		}

		return $output;
	}

	/**
	* Serialize a Diff.
	*
	* SYNOPSIS:
	*
	*  $diff = new Diff($lines1, $lines2);
	*  $string = $diff->serialize;
	*
	*  // recover Diff from serialized version:
	*  $diff2 = new Diff($string);
	*/
	function serialize ()
	{
		return serialize($this->edits);
	}

	/**
	* Return true if two files were equal.
	*/
	function isEmpty()
	{
		if (sizeof($this->edits) > 1)
		{
			return false;
		}

		if (sizeof($this->edits) == 0)
		{
			return true;
		}

		// Test for: only edit is a copy.
		return !is_array($this->edits[0]) && $this->edits[0] > 0;
	}

	/**
	* Compute the length of the Longest Common Subsequence (LCS).
	*
	* This is mostly for diagnostic purposed.
	*/
	function lcs()
	{
		$lcs = 0;
		for (reset($this->edits); $edit = current($this->edits); next($this->edits))
		{
			if (!is_array($edit) && $edit > 0)
			{
				$lcs += $edit;
			}
		}

		return $lcs;
	}

	/**
	* Check a Diff for validity.
	*
	* This is here only for debugging purposes.
	*/
	function _check ($from_lines, $to_lines)
	{
		$test = $this->apply($from_lines);
		if (serialize($test) != serialize($to_lines))
		{
			die("Diff::_check: failed");
		}

		reset($this->edits);
		$prev = current($this->edits);
		$prevtype = is_array($prev) ? 'a' : ($prev > 0 ? 'c' : 'd');

		while ($edit = next($this->edits))
		{
			$type = is_array($edit) ? 'a' : ($edit > 0 ? 'c' : 'd');
			if ($prevtype == $type)
			{
				die("Diff::_check: edit sequence is non-optimal");
			}
			$prevtype = $type;
		}
		$lcs = $this->lcs();
		echo "<strong>Diff Okay: LCS = $lcs</strong>\n";
	}
}

/**
* A class to format a Diff as HTML.
*
* Usage:
*
*	$diff = new Diff($lines1, $lines2); // compute diff.
*
*	$fmt = new DiffFormatter;
*	echo $fmt->format($diff, $lines1); // Output HTMLified standard diff.
*
*	or to output reverse diff (diff's that would take $lines2 to $lines1):
*
*	$fmt = new DiffFormatter(true);
*	echo $fmt->format($diff, $lines1);
*/
class DiffFormatter
{
	var $context_lines;
	var $do_reverse_diff;
	var $context_prefix, $deletes_prefix, $adds_prefix;

	function DiffFormatter ($reverse = false)
	{
		$this->do_reverse_diff = $reverse;
		$this->context_lines = 0;
		$this->context_prefix = '&nbsp;&nbsp;';
		$this->deletes_prefix = '&lt;&nbsp;';
		$this->adds_prefix = '&gt;&nbsp;';
	}

	function format ($diff, $from_lines)
	{
		$html = '<table width="100%" bgcolor="black" cellspacing=2 cellpadding=2 border=0>' . "\n";
		$html .= $this->_format($diff->edits, $from_lines);
		$html .= "</table>\n";

		return $html;
	}

	function _format ($edits, $from_lines)
	{
		$html = '';
		$x = 0; $y = 0;
		$xlim = sizeof($from_lines);

		reset($edits);
		while ($edit = current($edits))
		{
			if (!is_array($edit) && $edit >= 0)
			{ // Edit op is a copy.
				$ncopy = $edit;
			}
			else
			{
				$ncopy = 0;

				if (empty($hunk))
				{
					// Start of an output hunk.
					$xoff = max(0, $x - $this->context_lines);
					$yoff = $xoff + $y - $x;

					if ($xoff < $x)
					{
						// Get leading context.
						$context = array();

						for ($i = $xoff; $i < $x; $i++)
						{
							$context[] = $from_lines[$i];
						}
						$hunk['c'] = $context;
					}
				}

				if (is_array($edit))
				{
					// Edit op is an add.
					$y += sizeof($edit);
					$hunk[$this->do_reverse_diff ? 'd' : 'a'] = $edit;
				}
				else
				{
					// Edit op is a delete
					$deletes = array();

					while ($edit++ < 0)
					{
						$deletes[] = $from_lines[$x++];
					}

					$hunk[$this->do_reverse_diff ? 'a' : 'd'] = $deletes;
				}
			}

			$next = next($edits);

			if (!empty($hunk))
			{
				// Originally $ncopy > 2 * $this->context_lines, but we need to split as early as we can for creating MOD Text Templates. ;)
				if (!$next || $ncopy > $this->context_lines)
				{
					// End of an output hunk.
					$hunks[] = $hunk;
					unset($hunk);

					$xend = min($x + $this->context_lines, $xlim);

					if ($x < $xend)
					{
						// Get trailing context.
						$context = array();
						for ($i = $x; $i < $xend; $i++)
						{
							$context[] = $from_lines[$i];
						}
						$hunks[] = array('c' => $context);
					}

					$xlen = $xend - $xoff;
					$ylen = $xend + $y - $x - $yoff;
					$xbeg = $xlen ? $xoff + 1 : $xoff;
					$ybeg = $ylen ? $yoff + 1 : $yoff;

					if ($this->do_reverse_diff)
					{
						list ($xbeg, $xlen, $ybeg, $ylen) = array($ybeg, $ylen, $xbeg, $xlen);
					}

					$html .= $this->_emit_diff($xbeg, $xlen, $ybeg, $ylen, $hunks);
					unset($hunks);
				}
				else if ($ncopy)
				{
					$hunks[] = $hunk;

					// Copy context.
					$context = array();
					for ($i = $x; $i < $x + $ncopy; $i++)
					{
						$context[] = $from_lines[$i];
					}
					$hunk = array('c' => $context);
				}
			}

			$x += $ncopy;
			$y += $ncopy;
		}

		return $html;
	}

	function _emit_lines($lines,  $prefix, $color)
	{
		$html = '';
		reset($lines);
		while (list ($junk, $line) = each($lines))
		{
			$html .= "<tr bgcolor=\"$color\"><td><tt>$prefix</tt>";
			$html .= "<tt>" . htmlspecialchars($line) . "</tt></td></tr>\n";
		}
		return $html;
	}

	function _emit_diff ($xbeg,$xlen,$ybeg,$ylen,$hunks)
	{
		// Save hunk...
		$this->diff_hunks[] = $hunks;

		$html = '<tr><td><table width="100%" bgcolor="white" cellspacing="0" border="0" cellpadding="4">
				<tr bgcolor="#cccccc"><td><tt>' .
				$this->_diff_header($xbeg, $xlen, $ybeg, $ylen) . '
				</tt></td></tr>\n<tr><td>
				<table width="100%" cellspacing="0" border="0" cellpadding="2">
		';

		$prefix = array('c' => $this->context_prefix, 'a' => $this->adds_prefix, 'd' => $this->deletes_prefix);
		$color = array('c' => '#ffffff', 'a' => '#ffcccc', 'd' => '#ccffcc');

		for (reset($hunks); $hunk = current($hunks); next($hunks))
		{
			if (!empty($hunk['c']))
			{
				$html .= $this->_emit_lines($hunk['c'], $this->context_prefix, '#ffffff');
			}

			if (!empty($hunk['d']))
			{
				$html .= $this->_emit_lines($hunk['d'], $this->deletes_prefix, '#ccffcc');
			}

			if (!empty($hunk['a']))
			{
				$html .= $this->_emit_lines($hunk['a'], $this->adds_prefix, '#ffcccc');
			}
		}

		$html .= "</table></td></tr></table></td></tr>\n";
		return $html;
	}

	function _diff_header ($xbeg,$xlen,$ybeg,$ylen)
	{
		$what = $xlen ? ($ylen ? 'c' : 'd') : 'a';
		$xlen = $xlen > 1 ? "," . ($xbeg + $xlen - 1) : '';
		$ylen = $ylen > 1 ? "," . ($ybeg + $ylen - 1) : '';

		return "$xbeg$xlen$what$ybeg$ylen";
	}
}

/**
* A class to format a Diff as a pretty HTML unified diff.
*
* Usage:
*
*	$diff = new Diff($lines1, $lines2); // compute diff.
*
*	$fmt = new UnifiedDiffFormatter;
*	echo $fmt->format($diff, $lines1); // Output HTMLified unified diff.
*/
class UnifiedDiffFormatter extends DiffFormatter
{
	function UnifiedDiffFormatter ($reverse = false, $context_lines = 3)
	{
		$this->do_reverse_diff = $reverse;
		$this->context_lines = $context_lines;
		$this->context_prefix = '&nbsp;';
		$this->deletes_prefix = '-';
		$this->adds_prefix = '+';
	}

	function _diff_header ($xbeg,$xlen,$ybeg,$ylen)
	{
		$xlen = $xlen == 1 ? '' : ",$xlen";
		$ylen = $ylen == 1 ? '' : ",$ylen";

		return "@@ -$xbeg$xlen +$ybeg$ylen @@";
	}
}

/**
* A class to format a Diff as MOD Template instuctions.
*
* Usage:
*
*	$diff = new Diff($lines1, $lines2); // compute diff.
*
*	$fmt = new BBCodeDiffFormatter;
*	echo $fmt->format($diff, $lines1); // Output MOD Actions.
*/
class BBCodeDiffFormatter extends DiffFormatter
{
	function BBCodeDiffFormatter ($reverse = false, $context_lines = 3, $debug = false)
	{
		$this->do_reverse_diff = $reverse;
		$this->context_lines = $context_lines;
		$this->context_prefix = '&nbsp;';
		$this->deletes_prefix = '-';
		$this->adds_prefix = '+';
		$this->debug = $debug;
	}

	function format ($diff, $from_lines)
	{
		$html = $this->_format($diff->edits, $from_lines);

		return $html;
	}

	function skip_lines(&$order_array, &$ordering)
	{
		if (sizeof($order_array['find_c']))
		{
			$text = implode('', $order_array['find_c']);
			if ($text === "\n" || $text === "\t" || $text === '')
			{
				if (isset($order_array['first_find_c'][0]) &&
					is_array($order_array['first_find_c'][0]) &&
					trim(implode('', $order_array['first_find_c'][0])) != '' &&
					isset($order_array['replace']))
				{
					$order_array['add'] = $order_array['replace'];
					unset($order_array['replace']);
					// this is actually an after add
				}
				else
				{
					return true;
				}
			}
		}

		if (isset($order_array['add']) && sizeof($order_array['add']))
		{
			$text = implode('', $order_array['add']);
			if ($text === "\n" || $text === "\t" || $text === '')
			{
				return true;
			}
		}

		if (isset($order_array['replace']) && sizeof($order_array['replace']))
		{
			$text = implode('', $order_array['replace']);
			if ($text === "\n" || $text === "\t" || $text === '')
			{
				return true;
			}
		}
	}

	function _emit_lines_bb($ybeg, &$ordering)
	{
		$html = '';

		// Now adjust for bbcode display...
		foreach ($ordering as $order_array)
		{
			// Skip useless empty lines...
			if ($this->skip_lines($order_array, $ordering))
			{
				continue;
			}

			// Only include double-finds if the last find has very few code location indications...
			if (isset($order_array['first_find_c']) && sizeof($order_array['first_find_c']))
			{
				$text = implode('', $order_array['find_c']);
				if ($text === "\n" || $text === "\t" || $text === '')
				{
					// no real find, use first_find_c if possible!
					//var_dump($order_array);
					if (is_array($order_array['first_find_c'][0]))
					{
						$order_array['find_c'] = $order_array['first_find_c'][0];
					}
					else
					{
						if (isset($order_array['replace']) || isset($order_array['add']) || isset($order_array['delete']))
						{
							echo "skipped an edit!\n";
							var_dump($order_array);
						}
						continue;
					}
				}
				else
				{
					if (strlen(implode('', $order_array['find_c'])) < 50 && is_array($order_array['first_find_c'][0]))
					{
						$html .= "#\n#-----[ FIND ]---------------------------------------------\n# Around Line {$ybeg}\n";
						$html .= implode("", $order_array['first_find_c'][0]);
						$html .= "\n";
						$ybeg += sizeof($order_array['first_find_c'][0]);
					}
				}
			}

			// still here but nothing to do? what the heck?
			if (!isset($order_array['replace']) && !isset($order_array['add']) && !isset($order_array['delete']))
			{
				echo "skipped an edit!\n";
				var_dump($order_array);
				continue;
			}

			if (sizeof($order_array['find_c']))
			{
				$html .= "#\n#-----[ FIND ]---------------------------------------------\n# Around Line {$ybeg}\n";
				$html .= implode("", $order_array['find_c']);
				$html .= "\n";
			}

			if (isset($order_array['replace']))
			{
				$html .= "#\n#-----[ REPLACE WITH ]---------------------------------------------\n#\n";
				$html .= implode("", $order_array['replace']);
				$html .= "\n";
			}

			if (isset($order_array['add']))
			{
				$html .= "#\n#-----[ AFTER, ADD ]---------------------------------------------\n#\n";
				$html .= implode("", $order_array['add']);
				$html .= "\n";
			}

			// There is no DELETE. :o
			// Let's try to adjust it then...
			if (isset($order_array['delete']))
			{
				$text = implode('', $order_array['delete']);
				if ($text === "\n" || $text === "\t" || $text === '')
				{
					continue;
				}

				$ybeg += sizeof($order_array['find_c']);

				$html .= "#\n#-----[ FIND ]---------------------------------------------\n# Around Line {$ybeg}\n";
				$html .= implode("", $order_array['delete']);
				$html .= "\n";

				$html .= "#\n#-----[ REPLACE WITH ]---------------------------------------------\n# Just remove/delete the lines (replacing with an empty line)\n";
				$html .= "\n";
				$html .= "\n";
			}
		}

		return $html;
	}

	function format_open($filename)
	{
		$html = '';
		$html .= "#\n#-----[ OPEN ]--------------------------------------------- \n#\n{$filename}\n\n";

		return $html;
	}

	function format_close($filename)
	{
		return '';
	}

	function _emit_diff ($xbeg, $xlen, $ybeg, $ylen, $hunks)
	{

		// Go through the hunks to determine which method we are using (AFTER, ADD; REPLACE WITH or DELETE)

		// Remove the header...
		if (sizeof($hunks) <= 2 && !isset($hunks[1]['a']) && !isset($hunks[1]['d']))
		{
			$reorder = false;
			$orig_hunks = $hunks;

			foreach ($hunks as $key => $hunk)
			{
				if (isset($hunk['a']) && isset($hunk['d']))
				{
	/**/				if (sizeof($hunk['a']) == 1 && sizeof($hunk['d']) == 1)
					{
						if (preg_match('/\* @version \$Id:.+\$$/', $hunk['a'][0]) && preg_match('/\* @version \$Id:.+\$$/', $hunk['d'][0]))
						{
							// Only remove this sole hunk...
							unset($hunks[$key]);
							$reorder = true;
							continue;
						}
					}/**/

					// Compare the add and replace one...
					$string_1 = rtrim(trim(implode('', $hunk['a'])));
					$string_2 = rtrim(trim(implode('', $hunk['d'])));

					if (strcmp($string_1, $string_2) === 0)
					{
						// Only remove this sole hunk...
						unset($hunks[$key]);
						$reorder = true;
						continue;
					}
				}
			}

			if ($reorder)
			{
				// Now check if we have no more 'a' and 'd's
				$hunks = array_merge($hunks, array());
			}
		}
		else
		{
			$reorder = false;
			$orig_hunks = $hunks;

			foreach ($hunks as $key => $hunk)
			{
				if (isset($hunk['a']) && isset($hunk['d']))
				{
	/**/				if (sizeof($hunk['a']) == 1 && sizeof($hunk['d']) == 1)
					{
						if (preg_match('/\* @version \$Id:.+\$$/', $hunk['a'][0]) && preg_match('/\* @version \$Id:.+\$$/', $hunk['d'][0]))
						{
							// Only remove this sole hunk...
							unset($hunks[$key]);
							$reorder = true;
							continue;
						}
					}/**/

					// Compare the add and replace one...
					$string_1 = rtrim(trim(implode('', $hunk['a'])));
					$string_2 = rtrim(trim(implode('', $hunk['d'])));

					if (strcmp($string_1, $string_2) === 0)
					{
						// Only remove this sole hunk...
						unset($hunks[$key]);
						$reorder = true;
						continue;
					}
				}
			}

			if ($reorder)
			{
				$hunks = array_merge($hunks, array());

				if (sizeof($hunks) == 1 && sizeof($hunks[0]) == 1 && isset($hunks[0]['c']))
				{
					return;
				}
				else
				{
					$hunks = $orig_hunks;
				}
			}
		}

		if (sizeof($hunks) == 1 && sizeof($hunks[0]) == 1 && isset($hunks[0]['c']))
		{
			return;
		}

		$replace = false;
		foreach ($hunks as $key => $hunk)
		{
			if (isset($hunk['d']) && isset($hunk['a']))
			{
				$replace = true;
				break;
			}
		}

		$ordering = array();
		$cur_pos = 0;

		// Replace-block
		if ($replace)
		{
			foreach ($hunks as $hunk)
			{
				if (!isset($hunk['a']) && !isset($hunk['d']))
				{
					continue;
				}

				if (!empty($hunk['c']))
				{
					if (!isset($ordering[$cur_pos]['find_c']))
					{
						$ordering[$cur_pos]['find_c'] = $hunk['c'];
					}
					else
					{
						$ordering[$cur_pos]['end_c'] = $hunk['c'];
					}
				}

				// Make sure we begin fresh...
				if (!isset($ordering[$cur_pos]['replace']))
				{
					$ordering[$cur_pos]['first_find_c'][] = $ordering[$cur_pos]['find_c'];
					$ordering[$cur_pos]['find_c'] = array();
					$ordering[$cur_pos]['replace'] = array();
				}

				// Add the middle part if one exist...
				if (isset($ordering[$cur_pos]['end_c']))
				{
					$ordering[$cur_pos]['find_c'] = array_merge($ordering[$cur_pos]['find_c'], $ordering[$cur_pos]['end_c']);
					$ordering[$cur_pos]['replace'] = array_merge($ordering[$cur_pos]['replace'], $ordering[$cur_pos]['end_c']);
					unset($ordering[$cur_pos]['end_c']);
				}

				if (isset($hunk['d']))
				{
					$ordering[$cur_pos]['find_c'] = array_merge($ordering[$cur_pos]['find_c'], $hunk['d']);
				}

				if (isset($hunk['a']))
				{
					$ordering[$cur_pos]['replace'] = array_merge($ordering[$cur_pos]['replace'], $hunk['a']);
				}
			}
		}
		else
		{
			foreach ($hunks as $hunk)
			{
				if (!empty($hunk['c']))
				{
					if (!isset($ordering[$cur_pos]['find_c']))
					{
						$ordering[$cur_pos]['find_c'] = $hunk['c'];
					}
					else
					{
						$ordering[$cur_pos]['end_c'] = $hunk['c'];
					}
				}

				if (!empty($hunk['a']))
				{
					if (isset($ordering[$cur_pos]['delete']))
					{
						// If ordering is set with an delete entry, we will actually begin a new ordering array (to seperate delete from add)
						$cur_pos++;
						$ordering[$cur_pos]['find_c'] = $ordering[($cur_pos - 1)]['end_c'];
						$ordering[$cur_pos]['add'] = $hunk['a'];
					}
					else
					{
						if (isset($ordering[$cur_pos]['add']))
						{
							// Now, we really need to be quite careful here...
							if (isset($ordering[$cur_pos]['end_c']) && isset($hunk['c']) && isset($hunk['a']) && sizeof($hunk) == 2)
							{
								// There is a new find/add entry we did not catch... let's try to add a new entry then... but first check the hunk[a] contents...
								$text = trim(implode("\n", $hunk['c']));
								if ($text == "\n" || !$text)
								{
									$ordering[$cur_pos]['add'] = array_merge($ordering[$cur_pos]['add'], array("\n"), $hunk['a']);
								}
								else if (sizeof($hunk['c']) > 2 || strlen(implode('', $hunk['c'])) > 20)
								{
									$cur_pos++;
									$ordering[$cur_pos]['find_c'] = $ordering[($cur_pos - 1)]['end_c'];
									$ordering[$cur_pos]['add'] = $hunk['a'];
								}
								else
								{
									$cur_pos++;
									$ordering[$cur_pos]['find_c'] = $ordering[($cur_pos - 1)]['end_c'];
									$ordering[$cur_pos]['add'] = $hunk['a'];
/*									echo 'FIND STATEMENT TOO TINY';
									echo ";".rawurlencode($text).";";
									var_dump($hunk);
									exit;*/
								}
							}
							else
							{
								echo 'UNCATCHED ENTRY';
								var_dump($hunks);
								exit;
							}
						}
						else
						{
							$ordering[$cur_pos]['add'] = $hunk['a'];
						}
					}
				}
				else if (!empty($hunk['d']))
				{
					if (isset($ordering[$cur_pos]['add']))
					{
						// If ordering is set with an add entry, we will actually begin a new ordering array (to seperate delete from add)
						$cur_pos++;
						$ordering[$cur_pos]['find_c'] = $ordering[($cur_pos - 1)]['end_c'];
						$ordering[$cur_pos]['delete'] = $hunk['d'];
					}
					else
					{
						$ordering[$cur_pos]['delete'] = $hunk['d'];
					}
				}
			}
		}

		$html = '';

		return $this->_emit_lines_bb($ybeg, $ordering);
	}

	function _diff_header($xbeg, $xlen, $ybeg, $ylen)
	{
	}
}


/**
* A class to format a Diff as MOD Template instuctions.
*
* Usage:
*
*	$diff = new Diff($lines1, $lines2); // compute diff.
*
*	$fmt = new BBCodeDiffFormatter;
*	echo $fmt->format($diff, $lines1); // Output MOD Actions.
*/
class MODXDiffFormatter extends BBCodeDiffFormatter
{
	function MODXDiffFormatter ($reverse = false, $context_lines = 3, $debug = false)
	{
		$this->do_reverse_diff = $reverse;
		$this->context_lines = $context_lines;
		$this->context_prefix = '&nbsp;';
		$this->deletes_prefix = '-';
		$this->adds_prefix = '+';
		$this->debug = $debug;
	}

	function _emit_lines_bb($ybeg, &$ordering)
	{
		$html = '';

		// Now adjust for bbcode display...
		foreach ($ordering as $order_array)
		{
			// Skip useless empty lines...
			if ($this->skip_lines($order_array, $ordering))
			{
				continue;
			}

			// Only include double-finds if the last find has very few code location indications...
			if (sizeof($order_array['first_find_c']))
			{
				$text = implode('', $order_array['find_c']);
				if ($text === "\n" || $text === "\t" || $text === '')
				{
					continue;
				}

				if (strlen(implode('', $order_array['find_c'])) < 50)
				{
					if (substr($html, -8) !== '</find>' . "\n")
					{
						$html .= '	<edit>' . "\n";
					}

					$html .= '		<comment lang="en">Around Line ' . $ybeg . '</comment>' . "\n";
					$html .= '		<find>' . htmlspecialchars(implode('', $order_array['first_find_c'][0])) . '</find>' . "\n";
					$ybeg += sizeof($order_array['first_find_c'][0]);
				}
			}

			if (sizeof($order_array['find_c']))
			{
				if (substr($html, -8) !== '</find>' . "\n")
				{
					$html .= '	<edit>' . "\n";
				}

//				$html .= '	<edit>' . "\n";
				$html .= '		<comment lang="en">Around Line ' . $ybeg . '</comment>' . "\n";
				$html .= '		<find>' . htmlspecialchars(implode('', $order_array['find_c'])) . '</find>' . "\n";
			}

			if (isset($order_array['replace']))
			{
				$html .= '		<action type="replace-with">' . htmlspecialchars(implode('', $order_array['replace'])) . '</action>' . "\n";
				$html .= '	</edit>' . "\n";
			}

			if (isset($order_array['add']))
			{
				$html .= '		<action type="after-add">' . htmlspecialchars(implode('', $order_array['add'])) . '</action>' . "\n";
				$html .= '	</edit>' . "\n";
			}

			// There is no DELETE. :o
			// Let's try to adjust it then...
			if (isset($order_array['delete']))
			{
				$text = implode('', $order_array['delete']);
				if ($text === "\n" || $text === "\t" || $text === '')
				{
					continue;
				}

				$ybeg += sizeof($order_array['find_c']);

				if (substr($html, -8) !== '</find>' . "\n")
				{
					$html .= '	<edit>' . "\n";
				}
				$html .= '		<comment lang="en">Around Line ' . $ybeg . ' / Just remove/delete the lines (replacing with an empty line)</comment>' . "\n";
				$html .= '		<find>' . htmlspecialchars(implode('', $order_array['delete'])) . '</find>' . "\n";
				$html .= '		<action type="replace-with"></action>' . "\n";
				$html .= '	</edit>';
			}
		}

		return $html;
	}

	function format_open($filename)
	{
		return '<open src="' . $filename . '">' . "\n";
	}

	function format_close($filename)
	{
		return '</open>' . "\n";
	}

	function _diff_header($xbeg, $xlen, $ybeg, $ylen)
	{
	}
}

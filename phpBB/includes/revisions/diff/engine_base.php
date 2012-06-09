<?php

abstract class phpbb_revisions_diff_driver_base
{
	/**
	* Ending point for comparison
	* @var string
	*/
	protected $to;

	/**
	* Original text in comparison
	* @var string
	*/
	protected $from;

	/**
	* Diff granularity (i.e. character|word|line)
	* @var string
	*/
	protected $granularity;

	/**
	* The actual diff object
	* @var mixed
	*/
	protected $diff;

	/**
	* Diff operations array
	* @var array
	*/
	protected $operations;

	/**
	* The relative path to the phpBB root directory
	* @var string
	*/
	protected $phpbb_root_path;

	/**
	* The PHP extension
	* @var string
	*/
	protected $phpEx;

	/**
	* Constructor method
	*
	* @var string $from_text Original text in comparison
	* @var string $to_text Ending point for comparison
	*/
	function __construct($from_text, $to_text, $granularity = 'word')
	{
		global $phpbb_root_path, $phpEx;

		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;
		
		$this->to = $to_text;
		$this->from = $from_text;

		$this->get_diff_obj();
		$this->set_granularity($granularity);
		$this->process();
	}

	/**
	* Set the diff granularity
	*
	* @param string $granularity How fine-grained is the diff (i.e. character|word|line)
	* @return null
	*/
	abstract public function set_granularity($granularity);

	/**
	* Generate/Process a diff (not the output method, just the method that actually determines the diff)
	*
	* 	Note: This is purely a worker method; it should not actually return anything,
	*	but rather put the results in the $this->diff property
	*
	* @return null
	*/
	abstract public function process();

	/**
	* Render the diff
	*
	* @return string HTML formatted diff
	*/
	abstract public function render();

	/**
	* Count the number of additions between the two strings
	*
	* @return int Number of additions
	*/
	abstract public function additions_count();

	/**
	* Count the number of deletions between the two strings
	*
	* @return int Number of deletions
	*/
	abstract public function deletions_count();

	/**
	* Count the number of total edits/operations between the two strings
	*
	* @return int Number of edits
	*/
	abstract public function operations_count();

	/**
	* Sets the diff object to the $diff property
	*
	* @return null
	*/
	abstract protected function get_diff_obj();
}

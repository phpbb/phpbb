<?php

namespace vendor2\foo\migrations;

class foo implements \phpbb\db\migration\migration_interface
{
	/**
	 * {@inheritdoc}
	 */
	static public function depends_on()
	{
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function effectively_installed()
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function update_schema()
	{
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function revert_schema()
	{
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function update_data()
	{
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function revert_data()
	{
		return array();
	}
}

<?php
/**
*
* @package migration
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class jquery_update2 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->config['load_jquery_url'] !== '//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js';
	}

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\jquery_update',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('load_jquery_url', '//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js')),
		);
	}

}

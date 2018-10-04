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

use phpseclib\Crypt\RSA;

class acp_update
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $user, $template, $request;
		global $phpbb_root_path, $phpEx, $phpbb_container;

		$user->add_lang('install');

		$this->tpl_name = 'acp_update';
		$this->page_title = 'ACP_VERSION_CHECK';

		/* @var $version_helper \phpbb\version_helper */
		$version_helper = $phpbb_container->get('version_helper');
		try
		{
			$recheck = $request->variable('versioncheck_force', false);
			$updates_available = $version_helper->get_update_on_branch($recheck);
			$upgrades_available = $version_helper->get_suggested_updates();
			if (!empty($upgrades_available))
			{
				$upgrades_available = array_pop($upgrades_available);
			}
		}
		catch (\RuntimeException $e)
		{
			$template->assign_var('S_VERSIONCHECK_FAIL', true);

			$updates_available = array();
		}

		// TODO remove
		$updates_available['download'] = 'http://localhost/phpBB-3.2.3.zip';
		$updates_available['signature'] = 'http://localhost/phpBB-3.2.3.sig';

// generate keys
// $rsa = new RSA();
// $keys = $rsa->createKey();
// file_put_contents($phpbb_root_path . 'phpbb/update/key/key.private', $keys['privatekey']);
// file_put_contents($phpbb_root_path . 'phpbb/update/key/key', $keys['publickey']);
// print_r($keys);die;

// sign version
// $rsa = new RSA();
// $rsa->loadKey(file_get_contents($phpbb_root_path . 'phpbb/update/key/key.private'), RSA::PRIVATE_FORMAT_PKCS1);
// $rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);
// $hash = md5(file_get_contents('http://localhost/phpBB-3.2.3.zip'));
// $signature = $rsa->sign($hash);
// file_put_contents($phpbb_root_path . '../../phpBB-3.2.3.sig', $signature);
// die;

		if ($request->is_set('1clickupdate'))
		{
			if (empty($updates_available))
			{
				trigger_error($user->lang('NO_UPDATES_AVAILABLE') . adm_back_link($this->u_action));
			}

			if (confirm_box(true))
			{
				if (empty($updates_available['download']) || empty($updates_available['signature']))
				{
					trigger_error($user->lang('NO_1CLICKUPDATE') . adm_back_link($this->u_action));
				}

				// download new version
				$filename = 'phpBB-' . $updates_available['current'] . '.zip';
				$location = $phpbb_root_path . 'store/' . $filename;
				$client = new \GuzzleHttp\Client;
				$client->request('GET', $updates_available['download'], ['sink' => $location]);
				
				// verify integrity
				$signature_response = $client->request('GET', $updates_available['signature']);
				$signature = $signature_response->getBody();
				$rsa = new RSA();
				$rsa->loadKey(file_get_contents($phpbb_root_path . 'phpbb/update/key/key'), RSA::PRIVATE_FORMAT_PKCS1);
				$rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);
				$hash = md5(file_get_contents($location));
				if (!$rsa->verify($hash, $signature))
				{
					// remove file
					$filesystem = new \Symfony\Component\Filesystem\Filesystem();
					$filesystem->remove($location);

					trigger_error($user->lang('SIGNATURE_MISMATCH') . adm_back_link($this->u_action));
				}
				else
				{
					// unzip, replace, redirect to db updater
				}
			}
			else
			{
				confirm_box(false, $user->lang('CONFIRM_OPERATION'), build_hidden_fields(array(
					'i'						=> $id,
					'mode'					=> $mode,
					'1clickupdate'			=> true,
					'versioncheck_force'	=> true,
				)));
			}
		}

		if (!empty($updates_available))
		{
			$template->assign_block_vars('updates_available', $updates_available);
		}

		$update_link = $phpbb_root_path . 'install/app.' . $phpEx;

		$template->assign_vars(array(
			'S_UP_TO_DATE'			=> empty($updates_available),
			'U_ACTION'				=> $this->u_action,
			'U_VERSIONCHECK_FORCE'	=> append_sid($this->u_action . '&amp;versioncheck_force=1'),

			'CURRENT_VERSION'		=> $config['version'],
			'U_1CLICKUPDATE'		=> append_sid($this->u_action . '&amp;versioncheck_force=1&amp;1clickupdate=1'),

			'UPDATE_INSTRUCTIONS'	=> sprintf($user->lang['UPDATE_INSTRUCTIONS'], $update_link),
			'S_VERSION_UPGRADEABLE'		=> !empty($upgrades_available),
			'UPGRADE_INSTRUCTIONS'		=> !empty($upgrades_available) ? $user->lang('UPGRADE_INSTRUCTIONS', $upgrades_available['current'], $upgrades_available['announcement']) : false,
		));

		// Incomplete update?
		if (phpbb_version_compare($config['version'], PHPBB_VERSION, '<'))
		{
			$database_update_link = $phpbb_root_path . 'install/app.' . $phpEx . '/update';

			$template->assign_vars(array(
				'S_UPDATE_INCOMPLETE'		=> true,
				'FILES_VERSION'				=> PHPBB_VERSION,
				'INCOMPLETE_INSTRUCTIONS'	=> $user->lang('UPDATE_INCOMPLETE_EXPLAIN', $database_update_link),
			));
		}
	}
}

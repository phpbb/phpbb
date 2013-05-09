#!/usr/bin/env php
<?php
/**
*
* @package phpBB3
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

function show_usage()
{
	$filename = basename(__FILE__);

	echo "$filename merges a github pull request.\n";
	echo "\n";

	echo "Usage: [php] $filename -p pull_request_id [OPTIONS]\n";
	echo "\n";

	echo "Options:\n";
	echo " -p pull_request_id             The pull request id to be merged (mandatory)\n";
	echo " -r remote                      Remote of upstream, defaults to 'upstream' (optional)\n";
	echo " -d                             Outputs the commands instead of running them (optional)\n";
	echo " -h                             This help text\n";

	exit(2);
}

// Handle arguments
$opts = getopt('p:r:dh');

if (empty($opts) || isset($opts['h']))
{
	show_usage();
}

$pull_id	= get_arg($opts, 'p', '');
$remote		= get_arg($opts, 'r', 'upstream');
$dry_run	= !get_arg($opts, 'd', true);

try
{
	exit(work($pull_id, $remote));
}
catch (RuntimeException $e)
{
	echo $e->getMessage();
	exit($e->getCode());
}

function work($pull_id, $remote)
{
	// Get some basic data
	$pull = get_pull('phpbb', 'phpbb3', $pull_id);

	if (!$pull_id)
	{
		show_usage();
	}

	if ($pull['state'] != 'open')
	{
		throw new RuntimeException(sprintf("Error: pull request is closed\n",
			$target_branch), 5);
	}

	$pull_user = $pull['head'][0];
	$pull_branch = $pull['head'][1];
	$target_branch = $pull['base'][1];

	switch ($target_branch)
	{
		case 'develop-olympus':
			run("git checkout develop-olympus");
			run("git pull $remote develop-olympus");

			add_remote($pull_user, 'phpbb3');
			run("git fetch $pull_user");
			run("git merge --no-ff $pull_user/$pull_branch");
			run("phpBB/vendor/bin/phpunit");

			run("git checkout develop");
			run("git pull $remote develop");
			run("git merge --no-ff develop-olympus");
			run("phpBB/vendor/bin/phpunit");
		break;

		case 'develop':
			run("git checkout develop");
			run("git pull $remote develop");

			add_remote($pull_user, 'phpbb3');
			run("git fetch $pull_user");
			run("git merge --no-ff $pull_user/$pull_branch");
			run("phpBB/vendor/bin/phpunit");
		break;

		default:
			throw new RuntimeException(sprintf("Error: pull request target branch '%s' is not a main branch\n",
				$target_branch), 5);
		break;
	}
}

function add_remote($username, $repository, $pushable = false)
{
	$url = get_repository_url($username, $repository, false);
	run("git remote add $username $url", true);

	if ($pushable)
	{
		$ssh_url = get_repository_url($username, $repository, true);
		run("git remote set-url --push $username $ssh_url");
	}
}

function get_repository_url($username, $repository, $ssh = false)
{
	$url_base = ($ssh) ? 'git@github.com:' : 'git://github.com/';

	return $url_base . $username . '/' . $repository . '.git';
}

function api_request($query)
{
	$contents = file_get_contents("http://github.com/api/v2/json/$query");

	if ($contents === false)
	{
		throw new RuntimeException("Error: failed to retrieve pull request data\n", 4);
	}

	return json_decode($contents);
}

function get_pull($username, $repository, $pull_id)
{
	$request = api_request("pulls/$username/$repository/$pull_id");

	$pull = $request->pull;

	$pull_data = array(
		'base'  => array($pull->base->user->login, $pull->base->ref),
		'head'  => array($pull->head->user->login, $pull->head->ref),
		'state' => $pull->state,
	);

	return $pull_data;
}

function get_arg($array, $index, $default)
{
	return isset($array[$index]) ? $array[$index] : $default;
}

function run($cmd, $ignore_fail = false)
{
	global $dry_run;

	if (!empty($dry_run))
	{
		echo "$cmd\n";
	}
	else
	{
		passthru(escapeshellcmd($cmd), $status);

		if ($status != 0 && !$ignore_fail)
		{
			throw new RuntimeException(sprintf("Error: command '%s' failed with status %s'\n",
				$cmd, $status), 6);
		}
	}
}

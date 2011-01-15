<?php
/**
*
* @package phpBB3
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if ($argc < 2)
{
	show_usage($argv);
}

if (file_exists('.git'))
{
	echo "[error] git repository already exists\n";
	exit(1);
}

// Handle arguments
$scope		= get_arg($argv, 1, '');
$developer	= get_arg($argv, 2, '');

// Github setup
$username = 'phpbb';
$repository = 'phpbb3';

// Get some basic data
$network		= get_network($username, $repository);
$collaborators	= get_collaborators($username, $repository);

// Clone the blessed repository
clone_repository($username, $repository, isset($collaborators[$developer]));

switch ($scope)
{
	case 'collaborators':
		$remotes = array_intersect_key($network, $collaborators);
	break;

	case 'organisation':
		$remotes = array_intersect_key($network, get_organisation_members($username));
	break;

	case 'contributors':
		$remotes = array_intersect_key($network, get_contributors($username, $repository));
	break;

	case 'network':
		$remotes = $network;
	break;

	default:
		show_usage();
}

foreach ($remotes as $remote)
{
	if ($remote['username'] == $username)
	{
		// Skip blessed repository.
		continue;
	}

	add_remote($remote['username'], $remote['repository'], $remote['username'] == $developer);
}

run('git remote update');

function clone_repository($username, $repository, $pushable = false)
{
	$url = get_repository_url($username, $repository, false);
	run("git clone $url ./");

	if ($pushable)
	{
		$ssh_url = get_repository_url($username, $repository, true);
		run("git remote set-url --push origin $ssh_url");
	}
}

function add_remote($username, $repository, $pushable = false)
{
	$url = get_repository_url($username, $repository, false);
	run("git remote add $username $url");

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
	return json_decode(file_get_contents("http://github.com/api/v2/json/$query"));
}

function get_contributors($username, $repository)
{
	$request = api_request("repos/show/$username/$repository/contributors");

	$usernames = array();
	foreach ($request->contributors as $contributor)
	{
		$usernames[$contributor->login] = $contributor->login;
	}

	return $usernames;
}

function get_organisation_members($username)
{
	$request = api_request("organizations/$username/public_members");

	$usernames = array();
	foreach ($request->users as $member)
	{
		$usernames[$member->login] = $member->login;
	}

	return $usernames;
}

function get_collaborators($username, $repository)
{
	$request = api_request("repos/show/$username/$repository/collaborators");

	$usernames = array();
	foreach ($request->collaborators as $collaborator)
	{
		$usernames[$collaborator] = $collaborator;
	}

	return $usernames;
}

function get_network($username, $repository)
{
	$request = api_request("repos/show/$username/$repository/network");

	$usernames = array();
	foreach ($request->network as $network)
	{
		$usernames[$network->owner] = array(
			'username'		=> $network->owner,
			'repository'	=> $network->name,
		);
	}

	return $usernames;
}

function show_usage($argv)
{
	printf(
		"usage: php %s collaborators|organisation|contributors|network [your_github_username]\n",
		basename($argv[0])
	);
	exit(1);
}

function get_arg($argv, $index, $default)
{
	return isset($argv[$index]) ? $argv[$index] : $default;
}

function run($cmd)
{
	passthru(escapeshellcmd($cmd));
}

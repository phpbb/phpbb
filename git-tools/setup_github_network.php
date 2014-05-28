#!/usr/bin/env php
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

function show_usage()
{
	$filename = basename(__FILE__);

	echo "$filename adds repositories of a github network as remotes to a local git repository.\n";
	echo "\n";

	echo "Usage: [php] $filename -s collaborators|organisation|contributors|forks [OPTIONS]\n";
	echo "\n";

	echo "Scopes:\n";
	echo "  collaborators                 Repositories of people who have push access to the specified repository\n";
	echo "  contributors                  Repositories of people who have contributed to the specified repository\n";
	echo "  organisation                  Repositories of members of the organisation at github\n";
	echo "  forks                         All repositories of the whole github network\n";
	echo "\n";

	echo "Options:\n";
	echo " -s scope                       See description above (mandatory)\n";
	echo " -u github_username             Overwrites the github username (optional)\n";
	echo " -r repository_name             Overwrites the repository name (optional)\n";
	echo " -m your_github_username        Sets up ssh:// instead of git:// for pushable repositories (optional)\n";
	echo " -d                             Outputs the commands instead of running them (optional)\n";
	echo " -h                             This help text\n";

	exit(1);
}

// Handle arguments
$opts = getopt('s:u:r:m:dh');

if (empty($opts) || isset($opts['h']))
{
	show_usage();
}

$scope			= get_arg($opts, 's', '');
$username		= get_arg($opts, 'u', 'phpbb');
$repository 	= get_arg($opts, 'r', 'phpbb3');
$developer		= get_arg($opts, 'm', '');
$dry_run		= !get_arg($opts, 'd', true);
run(null, $dry_run);
exit(work($scope, $username, $repository, $developer));

function work($scope, $username, $repository, $developer)
{
	// Get some basic data
	$forks		= get_forks($username, $repository);
	$collaborators	= get_collaborators($username, $repository);

	if ($forks === false || $collaborators === false)
	{
		echo "Error: failed to retrieve forks or collaborators\n";
		return 1;
	}

	switch ($scope)
	{
		case 'collaborators':
			$remotes = array_intersect_key($forks, $collaborators);
		break;

		case 'organisation':
			$remotes = array_intersect_key($forks, get_organisation_members($username));
		break;

		case 'contributors':
			$remotes = array_intersect_key($forks, get_contributors($username, $repository));
		break;

		case 'forks':
			$remotes = $forks;
		break;

		default:
			show_usage();
	}

	if (file_exists('.git'))
	{
		add_remote($username, $repository, isset($collaborators[$developer]));
	}
	else
	{
		clone_repository($username, $repository, isset($collaborators[$developer]));
	}

	// Add private security repository for developers
	if ($username == 'phpbb' && $repository == 'phpbb3' && isset($collaborators[$developer]))
	{
		run("git remote add $username-security " . get_repository_url($username, "$repository-security", true));
	}

	// Skip blessed repository.
	unset($remotes[$username]);

	foreach ($remotes as $remote)
	{
		add_remote($remote['username'], $remote['repository'], $remote['username'] == $developer);
	}

	run('git remote update');
}

function clone_repository($username, $repository, $pushable = false)
{
	$url = get_repository_url($username, $repository, false);
	run("git clone $url ./ --origin $username");

	if ($pushable)
	{
		$ssh_url = get_repository_url($username, $repository, true);
		run("git remote set-url --push $username $ssh_url");
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
	return api_url_request("https://api.github.com/$query?per_page=100");
}

function api_url_request($url)
{
	$contents = file_get_contents($url, false, stream_context_create(array(
		'http' => array(
			'header' => "User-Agent: phpBB/1.0\r\n",
		),
	)));

	$sub_request_result = array();
	// Check headers for pagination links
	if (!empty($http_response_header))
	{
		foreach ($http_response_header as $header_element)
		{
			// Find Link Header which gives us a link to the next page
			if (strpos($header_element, 'Link: ') === 0)
			{
				list($head, $header_content) = explode(': ', $header_element);
				foreach (explode(', ', $header_content) as $links)
				{
					list($url, $rel) = explode('; ', $links);
					if ($rel == 'rel="next"')
					{
						// Found a next link, follow it and merge the results
						$sub_request_result = api_url_request(substr($url, 1, -1));
					}
				}
			}
		}
	}

	if ($contents === false)
	{
		return false;
	}
	$contents = json_decode($contents);

	if (isset($contents->message) && strpos($contents->message, 'API Rate Limit') === 0)
	{
		throw new RuntimeException('Reached github API Rate Limit. Please try again later' . "\n", 4);
	}

	return ($sub_request_result) ? array_merge($sub_request_result, $contents) : $contents;
}

function get_contributors($username, $repository)
{
	$request = api_request("repos/$username/$repository/stats/contributors");
	if ($request === false)
	{
		return false;
	}

	$usernames = array();
	foreach ($request as $contribution)
	{
		$usernames[$contribution->author->login] = $contribution->author->login;
	}

	return $usernames;
}

function get_organisation_members($username)
{
	$request = api_request("orgs/$username/public_members");
	if ($request === false)
	{
		return false;
	}

	$usernames = array();
	foreach ($request as $member)
	{
		$usernames[$member->login] = $member->login;
	}

	return $usernames;
}

function get_collaborators($username, $repository)
{
	$request = api_request("repos/$username/$repository/collaborators");
	if ($request === false)
	{
		return false;
	}

	$usernames = array();
	foreach ($request as $collaborator)
	{
		$usernames[$collaborator->login] = $collaborator->login;
	}

	return $usernames;
}

function get_forks($username, $repository)
{
	$request = api_request("repos/$username/$repository/forks");
	if ($request === false)
	{
		return false;
	}

	$usernames = array();
	foreach ($request as $fork)
	{
		$usernames[$fork->owner->login] = array(
			'username'		=> $fork->owner->login,
			'repository'	=> $fork->name,
		);
	}

	return $usernames;
}

function get_arg($array, $index, $default)
{
	return isset($array[$index]) ? $array[$index] : $default;
}

function run($cmd, $dry = false)
{
	static $dry_run;

	if (is_null($cmd))
	{
		$dry_run = $dry;
	}
	else if (!empty($dry_run))
	{
		echo "$cmd\n";
	}
	else
	{
		passthru(escapeshellcmd($cmd));
	}
}

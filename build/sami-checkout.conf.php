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

// Prevent 'Class "acm" does not exist.' exception on removeClass().
class PhpbbArrayStore extends Sami\Store\ArrayStore
{
	public function removeClass(Sami\Project $project, $name)
	{
		unset($this->classes[$name]);
	}
}

$iterator = Symfony\Component\Finder\Finder::create()
	->files()
	->name('*.php')
	->in(__DIR__ . '/../phpBB/')
	->notPath('#^cache/#')
	->notPath('#^develop/#')
	->notPath('#^ext/#')
	->notPath('#^vendor/#')
	->notPath('data')
;

$config = array(
	'theme'                => 'enhanced',
	'title'                => 'phpBB API Documentation',
	'build_dir'            => __DIR__.'/api/output/%version%',
	'cache_dir'            => __DIR__.'/api/cache/%version%',
	'default_opened_level' => 2,
	// Do not use JsonStore. See https://github.com/fabpot/Sami/issues/79
	'store'                => new PhpbbArrayStore,
);

return new Sami\Sami($iterator, $config);

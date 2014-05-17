<?php
/**
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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

$versions = Sami\Version\GitVersionCollection::create(__DIR__ . '/../')
	/*
	This would be nice, but currently causes various problems that need
	debugging.
	->addFromTags('release-3.0.*')
	->add('develop-olympus', '3.0-next (olympus)')
	->addFromTags('release-3.1.*')
	->add('develop-ascraeus', '3.1-next (ascraeus)')
	->add('develop')
	*/
	->add('develop-olympus')
	->add('develop-ascraeus')
;

return new Sami\Sami($iterator, array(
	'theme'                => 'enhanced',
	'versions'             => $versions,
	'title'                => 'phpBB API Documentation',
	'build_dir'            => __DIR__.'/api/output/%version%',
	'cache_dir'            => __DIR__.'/api/cache/%version%',
	'default_opened_level' => 2,
	// Do not use JsonStore. See https://github.com/fabpot/Sami/issues/79
	'store'                => new PhpbbArrayStore,
));

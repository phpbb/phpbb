<?php
/**
*
* @package extension
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* The extension metadata validator
*
* @package extension
*/
class phpbb_extension_metadata_validator
{
	protected $metadata;
	protected $schema;

	public function __construct()
	{
		$this->set_schema();
	}

	public function set_metadata($metadata)
	{
		$this->metadata = $metadata;

		return $metadata;
	}

	public function check_for_required_elements()
	{
		// Level 1 Required Elements
		$required_elements_level_1 = array('name', 'version', 'type', 'license', 'authors');

		foreach ($required_elements_level_1 as $element)
		{
			if(!($this->validate_existence($element)))
			{
				return false;
			}
		}

		// Level 2 Required Elements
		if(!($this->validate_existence('require','phpbb/phpbb')))
		{
			return false;
		}

		return true;
	}

	public function change_schema($element, $replacement)
	{
		$this->schema[$element] = $replacement;

		return $this->schema;
	}

	public function validate_metadata()
	{
		if (!isset($this->metadata))
		{
			throw new phpbb_extension_exception('Metadata is not set for validator');
		}

		if (!$this->check_for_required_elements())
		{
			return 'Not all required elements were found';
		}

		$first_level_values = array(
			'name',
			'description',
			'version',
			'type',
			'keywords',
			'homepage',
			'time',
			'license',
			'authors',
			'support',
			'require',
			'require-dev',
			'conflict',
			'replace',
			'provide',
			'suggest',
			'autoload',
			'include-path',
			'target-dir',
			'extra',
			'bin',
			'archive',
		);

		foreach ($this->metadata as $element)
		{
			if(!in_array($element, $first_level_values))
			{
				return "$element is invalid and not an acceptable top level item. If you have custom meta-data put them under 'extra'.";
			}

			switch ($element)
			{
				case 'name':
				case 'type':
				case 'version':
				case 'description':
				case 'homepage':
				case 'time':
				case 'license':
					if (!preg_match($this->schema[$element], $this->metadata[$element]))
					{
						return '$element metadata is invalid';
					}
				break;

				case 'authors':
					if(!$this->validate_authors())
					{
						return 'Authors were not valid';
					}
				break;

				case 'extra':
					if(!preg_match($this->schema['display-name'], $this->metadata['extra']['display-name']))
					{
						return 'Display name is invalid';
					}
				break;
			}
		}

		return true;
	}

	private function validate_authors()
	{
		if (!isset($this->metadata))
		{
			throw new phpbb_extension_exception('Metadata is not set for validator');
		}

		foreach ($this->metadata['authors'] as $author)
		{
			if (!isset($author['name']))
			{
				return false;
			}

			if (!preg_match($this->schema['author_name'], $author['name'])
				|| !preg_match($this->schema['author_role'], $author['role'])
				|| !preg_match($this->schema['author_homepage'], $author['homepage'])
				|| !preg_match($this->schema['author_email'], $author['email']));
			{
				return false;
			}
		}
	}

	private function set_basic_schema()
	{
		$this->schema = array(
			'name' => '^[a-zA-Z0-9_\x7f-\xff]{2,}/[a-zA-Z0-9_\x7f-\xff]{2,}$',
			'type' => 'phpbb-extension',
			'version' => 'v?(\d+\.\d+\.\d+?)(-(dev|beta|alpha)(\d*)?)?',
			'description' => '^{10,}$',
			'phpbb_version' => 'v?(\d+\.\d+\.\d+?)(-(dev|beta|alpha)(\d*)?)?',
			'php_version' => 'v?(\d+\.\d+\.\d+?)(-(dev|beta|alpha)(\d*)?)?',
			'display_name' => '^[a-zA-Z0-9_]{2,0}$',
			'homepage' => '([\d\w-.]+?\.(a[cdefgilmnoqrstuwz]|b[abdefghijmnorstvwyz]|c[acdfghiklmnoruvxyz]|d[ejkmnoz]|e[ceghrst]|f[ijkmnor]|g[abdefghilmnpqrstuwy]|h[kmnrtu]|i[delmnoqrst]|j[emop]|k[eghimnprwyz]|l[abcikrstuvy]|m[acdghklmnopqrstuvwxyz]|n[acefgilopruz]|om|p[aefghklmnrstwy]|qa|r[eouw]|s[abcdeghijklmnortuvyz]|t[cdfghjkmnoprtvwz]|u[augkmsyz]|v[aceginu]|w[fs]|y[etu]|z[amw]|aero|arpa|biz|com|coop|edu|info|int|gov|mil|museum|name|net|org|pro)(\b|\W(?<!&|=)(?!\.\s|\.{3}).*?))(\s|$',
			'time' => '\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?',
			'author_name' => '[a-zA-Z0-9\s]+',
			'author_role' => '[a-zA-Z0-9\s]+',
			'author_homepage' => '([\d\w-.]+?\.(a[cdefgilmnoqrstuwz]|b[abdefghijmnorstvwyz]|c[acdfghiklmnoruvxyz]|d[ejkmnoz]|e[ceghrst]|f[ijkmnor]|g[abdefghilmnpqrstuwy]|h[kmnrtu]|i[delmnoqrst]|j[emop]|k[eghimnprwyz]|l[abcikrstuvy]|m[acdghklmnopqrstuvwxyz]|n[acefgilopruz]|om|p[aefghklmnrstwy]|qa|r[eouw]|s[abcdeghijklmnortuvyz]|t[cdfghjkmnoprtvwz]|u[augkmsyz]|v[aceginu]|w[fs]|y[etu]|z[amw]|aero|arpa|biz|com|coop|edu|info|int|gov|mil|museum|name|net|org|pro)(\b|\W(?<!&|=)(?!\.\s|\.{3}).*?))(\s|$',
			'author_email' => '^[a-z0-9_\+-]+(\.[a-z0-9_\+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*\.([a-z]{2,4})$',
			'license' => 'GPL-2.0',
		);
	}

	private function validate_existence($element1, $element2 = '', $element3 = '')
	{
		if (!isset($this->metadata))
		{
			throw new phpbb_extension_exception('Metadata is not set for validator');
		}

		if ($element1 && !$element2 && !$element3)
		{
			if (!isset($this->metadata[$element1]))
			{
				return false;
			}
		}
		elseif ($element1 && $element2 && !$element3)
		{
			if (!isset($this->metadata[$element1][$element2]))
			{
				return false;
			}
		}
		elseif ($element1 && $element2 && $element3)
		{
			if (!isset($this->metadata[$element1][$element2][$element3]))
			{
				return false;
			}
		}
	}
}

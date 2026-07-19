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

namespace phpbb\tests\composer;

use Composer\Package\CompletePackage;
use Composer\Package\Link;
use Composer\Repository\ComposerRepository;
use Composer\Repository\FilterRepository;
use Composer\Repository\RepositorySet;
use Composer\Semver\Constraint\ConstraintInterface;
use Composer\Semver\VersionParser;
use phpbb\composer\installer;
use phpbb\composer\io\null_io;
use phpbb\filesystem\filesystem;
use phpbb\request\request;

class installer_test extends \phpbb_test_case
{
	public function test_packagist_keeps_configured_repositories_non_canonical(): void
	{
		$installer = $this->get_installer();
		$installer->set_repositories(['https://satis.phpbb.com']);
		$installer->set_packagist(true);

		$this->assertSame([[
			'type' => 'composer',
			'url' => 'https://satis.phpbb.com',
			'canonical' => false,
		]], $installer->get_repositories());
	}

	public function test_disabled_packagist_keeps_configured_repositories_canonical(): void
	{
		$installer = $this->get_installer();
		$installer->set_repositories(['https://satis.phpbb.com']);
		$installer->set_packagist(false);

		$this->assertSame([
			['packagist' => false],
			[
				'type' => 'composer',
				'url' => 'https://satis.phpbb.com',
				'canonical' => true,
			],
		], $installer->get_repositories());
	}

	public function test_unavailable_repository_is_skipped_when_fallback_is_allowed(): void
	{
		$available_config = [
			'type' => 'composer',
			'url' => 'https://available.example.com/',
			'canonical' => false,
		];
		$unavailable_config = [
			'type' => 'composer',
			'url' => 'https://unavailable.example.com/',
			'canonical' => false,
		];

		$available_repository = $this->get_repository($available_config);
		$available_repository->expects($this->once())
			->method('findPackages')
			->with('vendor/extension')
			->willReturn([]);

		$unavailable_repository = $this->get_repository($unavailable_config);
		$unavailable_repository->expects($this->once())
			->method('findPackages')
			->with('vendor/extension')
			->willThrowException(new \RuntimeException('HTTP 403'));

		$repositories = $this->get_installer()->filter_repositories(
			[$available_config, $unavailable_config],
			[
				new FilterRepository($available_repository, ['canonical' => false]),
				new FilterRepository($unavailable_repository, ['canonical' => false]),
			],
			['vendor/extension'],
			new null_io()
		);

		$this->assertSame([$available_config], $repositories);
	}

	public function test_extension_supporting_installer_v1_or_v2_is_compatible(): void
	{
		$versions = $this->get_compatible_versions($this->get_extension('^1.0 || ^2.0'));

		$this->assertArrayHasKey('phpbb/viglink', $versions);
	}

	public function test_extension_supporting_only_installer_v1_is_incompatible(): void
	{
		$versions = $this->get_compatible_versions($this->get_extension('^1.0'));

		$this->assertArrayNotHasKey('phpbb/viglink', $versions);
	}

	public function test_extension_with_missing_platform_requirement_is_incompatible(): void
	{
		$extension = $this->get_extension('^2.0');
		$requires = $extension->getRequires();
		$requires['ext-not-installed'] = $this->get_link('ext-not-installed', '*');
		$extension->setRequires($requires);

		$versions = $this->get_compatible_versions($extension);

		$this->assertArrayNotHasKey('phpbb/viglink', $versions);
	}

	public function test_extension_with_missing_package_requirement_is_incompatible(): void
	{
		$extension = $this->get_extension('^2.0');
		$requires = $extension->getRequires();
		$requires['example/missing'] = $this->get_link('example/missing', '^1.0');
		$extension->setRequires($requires);

		$versions = $this->get_compatible_versions($extension);

		$this->assertArrayNotHasKey('phpbb/viglink', $versions);
	}

	private function get_installer(): test_installer
	{
		return new test_installer('./', new filesystem(), new request(null, false));
	}

	private function get_repository(array $config): ComposerRepository
	{
		$repository = $this->getMockBuilder(ComposerRepository::class)
			->disableOriginalConstructor()
			->onlyMethods(['getRepoConfig', 'findPackages'])
			->getMock();
		$repository->method('getRepoConfig')->willReturn($config);

		return $repository;
	}

	private function get_extension(string $installer_constraint): CompletePackage
	{
		$extension = new CompletePackage('phpbb/viglink', 'dev-dev/4.0', 'dev-dev/4.0');
		$extension->setType('phpbb-extension');
		$extension->setRequires([
			'phpbb/phpbb' => $this->get_link('phpbb/phpbb', '^4.0'),
			'composer/installers' => $this->get_link('composer/installers', $installer_constraint),
		]);

		return $extension;
	}

	private function get_link(string $target, string $constraint): Link
	{
		return new Link('phpbb/viglink', $target, $this->parse_constraint($constraint), Link::TYPE_REQUIRE, $constraint);
	}

	private function parse_constraint(string $constraint): ConstraintInterface
	{
		return (new VersionParser())->parseConstraints($constraint);
	}

	private function get_compatible_versions(CompletePackage $extension): array
	{
		$installer = $this->get_installer();
		$method = new \ReflectionMethod(installer::class, 'get_compatible_versions');

		$phpbb_constraint = $this->parse_constraint('4.0.0-a3-dev');
		$installers_constraint = $this->parse_constraint('2.3.0');
		$provided_constraints = [
			'phpbb/phpbb' => [$phpbb_constraint],
			'composer/installers' => [$installers_constraint],
		];

		return $method->invoke(
			$installer,
			[],
			$phpbb_constraint,
			'dev',
			$installers_constraint,
			$provided_constraints,
			[],
			new RepositorySet('dev'),
			'phpbb/viglink',
			[$extension]
		);
	}
}

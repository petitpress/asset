<?php

declare(strict_types=1);

namespace SixtyEightPublishers\Asset\DI;

use Nette;
use LogicException;
use Nette\Utils\Validators;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Statement;
use Nette\Utils\AssertionException;

final class Config
{
	use Nette\SmartObject;

	private const MAIN_PACKAGE_DEFAULT = [
			'base_path' => '',
			'base_urls' => [],
			'version' => NULL,
			'version_format' => '%s?%s',
			'version_strategy' => NULL,
			'json_manifest_path' => NULL,
			'packages' => [],
		];

	private const PACKAGE_DEFAULTS = [
			'base_path' => NULL,
			'base_urls' => [],
			'version' => NULL,
			'version_format' => NULL,
			'version_strategy' => NULL,
			'json_manifest_path' => NULL,
		];

	public function getDefaults(): array
	{
		return self::MAIN_PACKAGE_DEFAULT;
	}

	/**
	 * @throws AssertionException
	 */
	public function getConfig(CompilerExtension $extension): array
	{
		$config = $this->mergeConfig($extension, $this->getDefaults(), $extension->getConfig());

		$config = $this->validatePackage($config, TRUE);

		foreach ($config['packages'] as $name => $package) {
			Validators::assert($package, 'array');

			$config['packages'][(string)$name] = $this->validatePackage(
				$this->mergeConfig($extension, self::PACKAGE_DEFAULTS, $package),
				FALSE
			);
		}

		return $config;
	}

	private function mergeConfig(CompilerExtension $extension, array $defaults, array $config): array
	{
		/** @noinspection PhpInternalEntityUsedInspection */
		return $extension->validateConfig(
			Nette\DI\Helpers::expand($defaults, $extension->getContainerBuilder()->parameters),
			$config
		);
	}

	/**
	 * @throws AssertionException
	 */
	private function validatePackage(array $package, bool $isDefault): array
	{
		Validators::assertField($package, 'version_strategy', 'string|null|' . Statement::class);
		Validators::assertField($package, 'version', 'string|null');
		Validators::assertField($package, 'version_format', TRUE === $isDefault ? 'string' : 'string|null');
		Validators::assertField($package, 'json_manifest_path', 'string|null');
		Validators::assertField($package, 'base_path', 'string|null');
		Validators::assertField($package, 'base_urls', 'string|string[]');

		if (is_string($package['base_urls'])) {
			$package['base_urls'] = [$package['base_urls']];
		}

		if (isset($package['version_strategy'], $package['version'])) {
			throw new LogicException(
				'You cannot use both "version_strategy" and "version" at the same time under "assets" packages.'
			);
		}

		if (isset($package['version_strategy'], $package['json_manifest_path'])) {
			throw new LogicException(
				'You cannot use both "version_strategy" and "json_manifest_path" at the same time under "assets" packages.'
			);
		}

		if (isset($package['version'], $package['json_manifest_path'])) {
			throw new LogicException(
				'You cannot use both "version" and "json_manifest_path" at the same time under "assets" packages.'
			);
		}

		return $package;
	}
}

<?php

declare(strict_types=1);

namespace SixtyEightPublishers\Asset\Tests\Helper;

use Tester\FileMock;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\Bridges\ApplicationDI\LatteExtension;
use SixtyEightPublishers\Asset\DI\AssetExtension;

final class ContainerFactory
{
	public static function createContainer(string $name, string|array $config): Container
	{
		if (!defined('TEMP_PATH')) {
			define('TEMP_PATH', __DIR__ . '/../temp');
		}

		$loader = new ContainerLoader(TEMP_PATH . '/Nette.Configurator_' . md5($name), TRUE);
		$class = $loader->load(static function (Compiler $compiler) use ($config): void {
			$compiler->addExtension('latte', new LatteExtension(TEMP_PATH . '/latte', TRUE));
			$compiler->addExtension('asset', new AssetExtension());
			$compiler->addConfig([
				'parameters' => [
					'filesDir' => realpath(__DIR__ . '/../files'),
				],
			]);

			if (is_array($config)) {
				$compiler->addConfig($config);
			} elseif (is_file($config)) {
				$compiler->loadConfig($config);
			} else {
				$compiler->loadConfig(FileMock::create((string) $config, 'neon'));
			}
		}, $name);

		return new $class();
	}
}

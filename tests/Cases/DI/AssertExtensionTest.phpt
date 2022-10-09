<?php

declare(strict_types=1);

namespace SixtyEightPublishers\Asset\Tests\Cases\DI;

use Exception;
use SixtyEightPublishers\Asset\Tests\Helper\ContainerFactory;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\UrlPackage;
use Tester;
use Symfony;
use SixtyEightPublishers;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class AssertExtensionTest extends Tester\TestCase
{

	public function testAssets(): void
	{
		$container = ContainerFactory::createContainer(__METHOD__, __DIR__ . '/../../files/assets.neon');

		/** @var Packages $packages */
		$packages = $container->getService('asset.packages');

		Assert::type(Packages::class, $packages);

		$this->assertPackage(
			$packages,
			null,
			UrlPackage::class,
			'http://cdn.example.com/my/image.png?version=SomeVersionScheme',
			'SomeVersionScheme'
		);

		$this->assertPackage(
			$packages,
			'images_path',
			PathPackage::class,
			'/foo/my/image.png?version=SomeVersionScheme',
			'SomeVersionScheme'
		);

		$this->assertPackage(
			$packages,
			'images',
			UrlPackage::class,
			'http://images1.example.com/my/image.png?version=1.0.0|http://images2.example.com/my/image.png?version=1.0.0',
			'1.0.0'
		);

		$this->assertPackage(
			$packages,
			'foo',
			PathPackage::class,
			'/my/image.png-1.0.0',
			'1.0.0'
		);

		$this->assertPackage(
			$packages,
			'bar',
			UrlPackage::class,
			'https://bar2.example.com/my/image.png?version=SomeVersionScheme',
			'SomeVersionScheme'
		);

		$this->assertPackage(
			$packages,
			'bar_version_strategy',
			UrlPackage::class,
			'https://bar_version_strategy.example.com/my/image.png-FOO',
			'-FOO'
		);

		$this->assertPackage(
			$packages,
			'json_manifest_strategy',
			PathPackage::class,
			'/my/image.abc123.png',
			'/my/image.abc123.png'
		);
	}

	public function testAssetsDefaultVersionStrategyAsService(): void
	{
		$container = ContainerFactory::createContainer(
			__METHOD__,
			__DIR__ . '/../../files/assets_version_strategy_as_service.neon'
		);

		/** @var Packages $packages */
		$packages = $container->getService('asset.packages');

		$this->assertPackage(
			$packages,
			null,
			UrlPackage::class,
			'http://cdn.example.com/my/image.png-FOO',
			'-FOO'
		);
	}

	private function assertPackage(
		Packages $packages,
		?string $name,
		string $type,
		string $url,
		string $version,
		string $path = 'my/image.png'
	): void {
		# package must be defined
		Assert::noError(static function () use ($packages, $name) {
			$packages->getPackage($name);
		});

		$package = $packages->getPackage($name);

		Assert::type($type, $package);
		Assert::same($version, $package->getVersion($path));

		if (str_contains($url, '|')) {
			Assert::contains($package->getUrl($path), explode('|', $url));
		} else {
			Assert::same($url, $package->getUrl($path));
		}
	}
}

(new AssertExtensionTest())->run();

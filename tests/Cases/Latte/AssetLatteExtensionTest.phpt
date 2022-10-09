<?php

declare(strict_types=1);

namespace SixtyEightPublishers\Asset\Tests\Cases\Latte;

use Latte;
use Latte\Engine;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\DI\Container;
use SixtyEightPublishers\Asset\Tests\Helper\ContainerFactory;
use Tester;
use SixtyEightPublishers;

require __DIR__ . '/../../bootstrap.php';

final class AssetLatteExtensionTest extends Tester\TestCase
{
	private ?Container $container;

	/**
	 * {@inheritdoc}
	 */
	protected function setUp(): void
	{
		parent::setUp();

		$this->container = ContainerFactory::createContainer(__METHOD__, __DIR__ . '/../../files/assets.neon');
	}

	public function testAssetMacro(): void
	{
		$latte = $this->createLatte();

		Tester\Assert::same(
			'http://cdn.example.com/my/first/file.png?version=SomeVersionScheme',
			$latte->renderToString('{asset "my/first/file.png"}')
		);
		Tester\Assert::same(
			'/my/second/file.abc123.png',
			$latte->renderToString('{asset "my/second/file.png", "json_manifest_strategy"}')
		);
	}

	public function testAssetVersionMacro(): void
	{
		$latte = $this->createLatte();

		Tester\Assert::same('SomeVersionScheme', $latte->renderToString('{asset_version "my/first/file.png"}'));
		Tester\Assert::same('1.0.0', $latte->renderToString('{asset_version "my/second/file.png", "images"}'));
	}

	private function createLatte(): Engine
	{
		/** @var LatteFactory $latteFactory */
		$latteFactory = $this->container->getService('latte.latteFactory');
		$latte = $latteFactory->create();
		$latte->setLoader(new Latte\Loaders\StringLoader());

		return $latte;
	}
}

(new AssetLatteExtensionTest())->run();

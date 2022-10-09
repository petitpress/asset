<?php

declare(strict_types=1);

namespace SixtyEightPublishers\Asset\DI;

use Nette;
use Nette\DI\Definitions\Statement;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\PackageInterface;

final class PackageDefinitionFacade
{
	use Nette\SmartObject;

	private ReferenceFacade $referenceFacade;

	public function __construct(ReferenceFacade $referenceFacade)
	{
		$this->referenceFacade = $referenceFacade;
	}

	public function createPackageStatement(
		string $name,
		?string $basePath,
		array $baseUrls,
		Statement $versionStrategy
	): Statement {
		if (!empty($basePath) && !empty($baseUrls)) {
			throw new \LogicException('An asset package cannot have base URLs and base paths.');
		}

		if (empty($baseUrls)) {
			return new Statement(
				$this->getPackageDependencyReference(
					new Statement(PathPackage::class, [
						'basePath' => (string)$basePath,
						'versionStrategy' => $versionStrategy,
					]),
					$name
				)
			);
		}

		return new Statement(
			$this->getPackageDependencyReference(
				new Statement(UrlPackage::class, [
					'baseUrls' => $baseUrls,
					'versionStrategy' => $versionStrategy,
				]),
				$name
			)
		);
	}

	public function getPackageDependencyReference(string|Statement $definition, string $packageName): string
	{
		return $this->referenceFacade->getDependencyReference(
			$definition,
			'package.' . $packageName,
			PackageInterface::class
		);
	}
}

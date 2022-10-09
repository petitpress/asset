<?php

declare(strict_types=1);

namespace SixtyEightPublishers\Asset\DI;

use Nette;
use Symfony;
use Nette\DI\Definitions\Statement;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;

final class VersionDefinitionFacade
{
	use Nette\SmartObject;

	private ReferenceFacade $referenceFacade;

	public function __construct(ReferenceFacade $referenceFacade)
	{
		$this->referenceFacade = $referenceFacade;
	}

	public function createVersionStatement(
		string $name,
		?string $version,
		string $format,
		?string $jsonManifestPath
	): Statement {
		// Configuration prevents $version and $jsonManifestPath from being set
		if (NULL !== $version) {
			$reference = $this->getVersionDependencyReference(
				new Statement(StaticVersionStrategy::class, [
					'version' => $version,
					'format' => $format,
				]),
				$name
			);
		}

		if (NULL !== $jsonManifestPath) {
			$reference = $this->getVersionDependencyReference(
				new Statement(JsonManifestVersionStrategy::class, [
					'manifestPath' => $jsonManifestPath,
				]),
				$name
			);
		}

		return new Statement(
			$reference ?? $this->getVersionDependencyReference(
				Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy::class,
				$name
			)
		);
	}

	public function getVersionDependencyReference(string|Statement $definition, string $versionName): string
	{
		return $this->referenceFacade->getDependencyReference(
			$definition,
			'version_strategy.' . $versionName,
			VersionStrategyInterface::class
		);
	}
}

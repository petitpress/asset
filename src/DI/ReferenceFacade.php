<?php

declare(strict_types=1);

namespace SixtyEightPublishers\Asset\DI;

use Nette;
use Nette\Utils\Strings;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Statement;
use Nette\DI\Extensions\InjectExtension;

final class ReferenceFacade
{
	use Nette\SmartObject;

	private CompilerExtension $extension;

	public function __construct(CompilerExtension $extension)
	{
		$this->extension = $extension;
	}

	public function getDependencyReference(string|Statement $definition, string $registrationName, string $type): string
	{
		if (!is_string($definition) || !Strings::startsWith($definition, '@')) {
			$this->extension
				->getContainerBuilder()
				->addDefinition($registrationName = $this->extension->prefix($registrationName))
				->setType($type)
				->setFactory($definition)
				->addTag(InjectExtension::TAG_INJECT);

			return '@' . $registrationName;
		}

		return $definition;
	}
}

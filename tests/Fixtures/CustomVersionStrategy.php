<?php

declare(strict_types=1);

namespace SixtyEightPublishers\Asset\Tests\Fixtures;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

final class CustomVersionStrategy implements VersionStrategyInterface
{
	private string $postfix;

	public function __construct(string $postfix)
	{
		$this->postfix = $postfix;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getVersion(string $path): string
	{
		return $this->postfix;
	}

	/**
	 * {@inheritdoc}
	 */
	public function applyVersion(string $path): string
	{
		return $path . $this->postfix;
	}
}

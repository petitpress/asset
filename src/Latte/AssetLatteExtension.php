<?php

declare(strict_types=1);

namespace SixtyEightPublishers\Asset\Latte;

use Latte\Extension;
use Latte\Compiler\Tag;
use Latte\Compiler\Node;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Nodes\AuxiliaryNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;

class AssetLatteExtension extends Extension
{
	/**
	 * @return array<string, callable>
	 */
	public function getTags(): array
	{
		return [
			'asset' => [$this, 'macroAsset'],
			'asset_version' => [$this, 'macroAssetVersion'],
		];
	}

	public function macroAsset(Tag $tag): Node
	{
		$args = [];
		$assetPath = $tag->parser->parseUnquotedStringOrExpression();

		if (!$tag->parser->isEnd()) {
			$tag->parser->stream->tryConsume(',');
			$args = [$tag->parser->parseExpression()];
		}

		return new AuxiliaryNode(
			fn (PrintContext $context) => $context->format(
				'echo %escape($this->global->symfonyPackages->getUrl(%node, %args));',
				$assetPath,
				$args
			)
		);
	}

	public function macroAssetVersion(Tag $tag): Node
	{
		$args = [];
		$assetPath = $tag->parser->parseUnquotedStringOrExpression();

		if (!$tag->parser->isEnd()) {
			$tag->parser->stream->tryConsume(',');
			$args = [$tag->parser->parseExpression()];
		}

		return new AuxiliaryNode(
			fn (PrintContext $context) => $context->format(
				'echo %escape($this->global->symfonyPackages->getVersion(%node, %args));',
				$assetPath,
				$args
			)
		);
	}
}

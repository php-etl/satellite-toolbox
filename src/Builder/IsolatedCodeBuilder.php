<?php

declare(strict_types=1);

namespace Kiboko\Component\SatelliteToolbox\Builder;

use PhpParser\Builder;
use PhpParser\Node;

final readonly class IsolatedCodeBuilder implements Builder
{
    public function __construct(
        private array $stmts
    ) {}

    public function getNode(): Node
    {
        return new Node\Expr\FuncCall(
            new Node\Expr\Closure([
                'stmts' => $this->stmts,
            ])
        );
    }
}

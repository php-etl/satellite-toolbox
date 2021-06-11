<?php declare(strict_types=1);

namespace Kiboko\Component\SatelliteToolbox\Builder;

use PhpParser\Builder;
use PhpParser\Node;

final class IsolatedCodeBuilder implements Builder
{
    /** @var iterable<Node> */
    private iterable $usedVariables;

    public function __construct(
        private array $stmts,
        Node ...$usedVariables,
    ) {
        $this->usedVariables = $usedVariables;
    }

    public function getNode(): Node
    {
        return new Node\Expr\FuncCall(
            new Node\Expr\Closure([
                'stmts' => $this->stmts,
                'uses' => $this->usedVariables,
            ])
        );
    }
}

<?php

declare(strict_types=1);

namespace Kiboko\Component\SatelliteToolbox\Builder;

use PhpParser\Builder;
use PhpParser\Node;

final class IsolatedFuncCallAppendingBuilder implements Builder
{
    /** @var Node\Expr[] */
    private readonly array $usedVariables;

    public function __construct(
        private readonly Node\Expr $input,
        private readonly array $stmts,
        Node\Expr ...$usedVariables
    ) {
        $this->usedVariables = $usedVariables;
    }

    public function getNode(): Node
    {
        return new Node\Stmt\Expression(
            new Node\Expr\FuncCall(
                new Node\Expr\Closure([
                    'params' => [
                        new Node\Param(
                            var: new Node\Expr\Variable('input'),
                        ),
                        ...$this->usedVariables,
                    ],
                    'stmts' => $this->stmts,
                    'uses' => [
                        new Node\Expr\Variable('output'),
                    ],
                ]),
                [
                    new Node\Arg($this->input),
                    ...$this->usedVariables,
                ]
            ),
        );
    }
}

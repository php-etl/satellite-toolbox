<?php

declare(strict_types=1);

namespace Kiboko\Component\SatelliteToolbox\Builder;

use PhpParser\Builder;
use PhpParser\Node;

final class IsolatedValueTransformationBuilder implements Builder
{
    /** @var Node\Expr[] */
    private readonly array $usedVariables;

    public function __construct(
        private readonly Node\Expr $input,
        private readonly Node\Expr $output,
        private readonly array $stmts,
        Node\Expr ...$usedVariables
    ) {
        $this->usedVariables = $usedVariables;
    }

    public function getNode(): Node
    {
        return new Node\Stmt\Expression(
            new Node\Expr\Assign(
                $this->output,
                new Node\Expr\FuncCall(
                    new Node\Expr\Closure([
                        'params' => [
                            new Node\Param(
                                var: new Node\Expr\Variable('input'),
                            ),
                        ],
                        'stmts' => $this->stmts,
                        'uses' => $this->usedVariables,
                    ]),
                    [
                        new Node\Arg($this->input),
                    ],
                ),
            )
        );
    }
}

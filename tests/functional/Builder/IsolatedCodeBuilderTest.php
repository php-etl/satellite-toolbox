<?php

namespace functional\Builder;

use Kiboko\Component\PHPUnitExtension\BuilderAssertTrait;
use Kiboko\Component\SatelliteToolbox\Builder\IsolatedCodeBuilder;
use PhpParser\Node;
use PHPUnit\Framework\TestCase;
use Vfs\FileSystem;

final class IsolatedCodeBuilderTest extends TestCase
{
    use BuilderAssertTrait;

    private ?FileSystem $fs = null;

    protected function setUp(): void
    {
        $this->fs = FileSystem::factory('vfs://');
        $this->fs->mount();
    }

    protected function tearDown(): void
    {
        $this->fs->unmount();
        $this->fs = null;
    }

    public function testBuilderWithoutUseStatements(): void
    {
        $builder = new IsolatedCodeBuilder([
            new Node\Stmt\Expression(
                new Node\Expr\Assign(
                    var: new Node\Expr\Variable('output'),
                    expr: new Node\Expr\Array_(
                        items: [
                            new Node\Scalar\String_('myFirstData'),
                            new Node\Scalar\String_('mySecondData')
                        ],
                        attributes: [
                            'kind' => Node\Expr\Array_::KIND_SHORT
                        ]
                    )
                )
            ),
            new Node\Stmt\Return_(
                expr: new Node\Expr\Variable('output')
            )
        ]);

        $this->assertBuilderProducesPipelineExtractingLike(
           [
               'myFirstData',
               'mySecondData'
           ],
            $builder
        );
    }
}

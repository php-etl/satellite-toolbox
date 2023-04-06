<?php

namespace functional\Builder;

use Kiboko\Component\PHPUnitExtension\Assert\ExtractorBuilderAssertTrait;
use Kiboko\Component\PHPUnitExtension\PipelineRunner;
use Kiboko\Component\SatelliteToolbox\Builder\IsolatedCodeBuilder;
use Kiboko\Contract\Pipeline\PipelineRunnerInterface;
use PhpParser\Node;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;

final class IsolatedCodeBuilderTest extends TestCase
{
    use ExtractorBuilderAssertTrait;

    private ?vfsStreamDirectory $fs = null;

    protected function setUp(): void
    {
        $this->fs = vfsStream::setup();
    }

    protected function tearDown(): void
    {
        $this->fs = null;
        vfsStreamWrapper::unregister();
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

        $this->assertBuildsExtractorExtractsLike(
           [
               'myFirstData',
               'mySecondData'
           ],
            $builder
        );
    }

    protected function getBuilderCompilePath(): string
    {
        return $this->fs->url();
    }

    public function pipelineRunner(): PipelineRunnerInterface
    {
        return new PipelineRunner();
    }
}

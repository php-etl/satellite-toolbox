<?php

namespace functional;

use PhpParser\Node;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use function Kiboko\Component\SatelliteToolbox\Configuration\compileValue;

class ConfigurationTest extends TestCase
{
    private ExpressionLanguage $interpreter;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->interpreter = new ExpressionLanguage();
    }

    public function testCompileValueAsString() {
        $actual = compileValue($this->interpreter, 'bar');

        $this->assertEquals(
            new Node\Scalar\String_('bar'),
            $actual
        );
    }

    public function testCompileValueAsInteger() {
        $actual = compileValue($this->interpreter, 10);

        $this->assertEquals(
            new Node\Scalar\LNumber(10),
            $actual
        );
    }

    public function testCompileValueAsExpression() {
        $actual = compileValue($this->interpreter, new Expression('input == "abc"'));

        $this->assertEquals(
            new Node\Expr\BinaryOp\Equal(
                left: New Node\Expr\Variable(
                    name: 'input',
                    attributes: [
                        'startLine' => 1,
                        'endLine' => 1
                    ]
                ),
                right: new Node\Scalar\String_(
                    value: 'abc',
                    attributes: [
                        'startLine' => 1,
                        'endLine' => 1,
                        'kind' => 2
                    ]
                ),
                attributes: [
                    'startLine' => 1,
                    'endLine' => 1
                ]
            ),
            $actual
        );
    }
}

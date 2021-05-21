<?php

namespace Kiboko\Component\SatelliteToolbox\Configuration;

use PhpParser\ParserFactory;
use PhpParser\Node;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

function isExpression(): callable {
    return fn ($data) => is_string($data) && $data !== '' && str_starts_with($data, '@=');
}

function asExpression(): callable {
    return fn ($data) => new Expression(substr($data, 2));
}

function compileExpression(
    ExpressionLanguage $interpreter,
    Expression $value,
    string ...$additionalVariables
): Node\Expr {
    if ($value instanceof Expression) {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, null);
        return $parser->parse('<?php ' . $interpreter->compile($value, ['input', ...$additionalVariables]) . ';')[0]->expr;
    }

    return new Node\Scalar\String_($value);
}

function compileValueWhenExpression(
    ExpressionLanguage $interpreter,
    string|Expression $value,
    string ...$additionalVariables
): Node\Expr {
    if ($value instanceof Expression) {
        compileExpression($interpreter, $value, ...$additionalVariables);
    }

    return new Node\Scalar\String_($value);
}

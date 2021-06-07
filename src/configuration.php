<?php

namespace Kiboko\Component\SatelliteToolbox\Configuration;

use Kiboko\Contract\Configurator\InvalidConfigurationException;
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
    $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, null);
    return $parser->parse('<?php ' . $interpreter->compile($value, ['input', ...$additionalVariables]) . ';')[0]->expr;
}

function compileValueWhenExpression(
    ExpressionLanguage $interpreter,
    string|int|float|bool|Expression $value,
    string ...$additionalVariables
): Node\Expr {
    if (is_string($value)) {
        return new Node\Scalar\String_(value: $value);
    }
    if (is_int($value)) {
        return new Node\Scalar\LNumber(value: $value);
    }
    if (is_double($value)) {
        return new Node\Scalar\DNumber(value: $value);
    }
    if ($value === true) {
        return new Node\Expr\ConstFetch(
            name: new Node\Name(name: 'true'),
        );
    }
    if ($value === false) {
        return new Node\Expr\ConstFetch(
            name: new Node\Name(name: 'false'),
        );
    }
    if ($value instanceof Expression) {
        return compileExpression($interpreter, $value, ...$additionalVariables);
    }

    throw new InvalidConfigurationException(
        message: 'Could not determine the correct way to compile the provided filter.',
    );
}

function compileValue(ExpressionLanguage $interpreter, null|bool|string|int|float|array|Expression $value): Node\Expr
{
    if ($value === null) {
        return new Node\Expr\ConstFetch(
            name: new Node\Name(name: 'null'),
        );
    }
    if ($value === true) {
        return new Node\Expr\ConstFetch(
            name: new Node\Name(name: 'true'),
        );
    }
    if ($value === false) {
        return new Node\Expr\ConstFetch(
            name: new Node\Name(name: 'false'),
        );
    }
    if (is_string($value)) {
        return new Node\Scalar\String_(value: $value);
    }
    if (is_int($value)) {
        return new Node\Scalar\LNumber(value: $value);
    }
    if (is_double($value)) {
        return new Node\Scalar\DNumber(value: $value);
    }
    if (is_array($value)) {
        return compileArray(interpreter: $interpreter, values: $value);
    }
    if ($value instanceof Expression) {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, null);
        return $parser->parse('<?php ' . $interpreter->compile($value, ['input']) . ';')[0]->expr;
    }

    throw new InvalidConfigurationException(
        message: 'Could not determine the correct way to compile the provided filter.',
    );
}

/** @internal */
function compileArray(ExpressionLanguage $interpreter, array $values): Node\Expr
{
    $items = [];
    foreach ($values as $key => $value) {
        $keyNode = null;
        if (is_string($key)) {
            $keyNode = new Node\Scalar\String_($key);
        }

        $items[] = new Node\Expr\ArrayItem(
            value: compileValue($interpreter, $value),
            key: $keyNode,
        );
    }

    return new Node\Expr\Array_(
        $items,
        [
            'kind' => Node\Expr\Array_::KIND_SHORT,
        ]
    );
}

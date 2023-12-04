<?php

declare(strict_types=1);

namespace Kiboko\Component\SatelliteToolbox\Configuration;

use Kiboko\Contract\Configurator\InvalidConfigurationException;
use PhpParser\Node;
use PhpParser\ParserFactory;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

function isExpression(): callable
{
    return fn ($data) => \is_string($data) && '' !== $data && str_starts_with($data, '@=');
}

function asExpression(): callable
{
    return fn ($data) => new Expression(substr((string) $data, 2));
}

function compileExpression(
    ExpressionLanguage $interpreter,
    Expression $value,
    string ...$additionalVariables
): Node\Expr {
    $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);

    /** @var Node\Stmt\Expression $statement */
    $statement = $parser->parse('<?php '.$interpreter->compile($value, ['input', ...$additionalVariables]).';')[0];

    return $statement->expr;
}

function compileValueWhenExpression(
    ExpressionLanguage $interpreter,
    null|array|bool|Expression|float|int|string $value,
    string ...$additionalVariables
): Node\Expr {
    if (\is_string($value)) {
        return new Node\Scalar\String_(value: $value);
    }
    if (\is_int($value)) {
        return new Node\Scalar\LNumber(value: $value);
    }
    if (\is_float($value)) {
        return new Node\Scalar\DNumber(value: $value);
    }
    if (null === $value) {
        return new Node\Expr\ConstFetch(
            name: new Node\Name(name: 'null'),
        );
    }
    if (true === $value) {
        return new Node\Expr\ConstFetch(
            name: new Node\Name(name: 'true'),
        );
    }
    if (false === $value) {
        return new Node\Expr\ConstFetch(
            name: new Node\Name(name: 'false'),
        );
    }
    if (\is_array($value)) {
        return compileArray($interpreter, $value, ...$additionalVariables);
    }
    if ($value instanceof Expression) {
        return compileExpression($interpreter, $value, ...$additionalVariables);
    }

    throw new InvalidConfigurationException(message: 'Could not determine the correct way to compile the provided filter.');
}

/** @deprecated since Satellite toolbox 0.1, use Kiboko\Component\SatelliteToolbox\Configuration\compileValueWhenExpression instead. */
function compileValue(ExpressionLanguage $interpreter, null|array|bool|Expression|float|int|string $value): Node\Expr
{
    @trigger_error('The '.__NAMESPACE__.'\compileValue function is deprecated since version 0.1 and will be removed in a later version. Use '.__NAMESPACE__.'\compileValueWhenExpression instead.', \E_USER_DEPRECATED);

    return compileValueWhenExpression($interpreter, $value);
}

/** @internal */
function compileArray(
    ExpressionLanguage $interpreter,
    array $values,
    string ...$additionalVariables,
): Node\Expr {
    $items = [];
    foreach ($values as $key => $value) {
        $keyNode = null;
        if (\is_string($key)) {
            $keyNode = new Node\Scalar\String_($key);
        }

        $items[] = new Node\Expr\ArrayItem(
            value: compileValueWhenExpression($interpreter, $value, ...$additionalVariables),
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

function mutuallyExclusiveFields(string ...$exclusions): \Closure
{
    return function (array $value) use ($exclusions) {
        $fields = [];
        foreach ($exclusions as $exclusion) {
            if (\array_key_exists($exclusion, $value)) {
                $fields[] = $exclusion;
            }

            if (\count($fields) < 2) {
                continue;
            }

            throw new \InvalidArgumentException(sprintf('Your configuration should either contain the "%s" or the "%s" field, not both.', ...$fields));
        }

        return $value;
    };
}

function mutuallyDependentFields(string $field, string ...$dependencies): \Closure
{
    return function (array $value) use ($field, $dependencies) {
        if (!\array_key_exists($field, $value)) {
            return $value;
        }

        foreach ($dependencies as $dependency) {
            if (!\array_key_exists($dependency, $value)) {
                throw new \InvalidArgumentException(sprintf('Your configuration should contain the "%s" field if the "%s" field is present.', $dependency, $field));
            }
        }

        return $value;
    };
}

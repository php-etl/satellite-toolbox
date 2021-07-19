<?php

namespace Kiboko\Component\SatelliteToolbox\AST;

use PhpParser\Node;

function variable(string $name): Node\Expr\Variable
{
    return new Node\Expr\Variable(uniqid($name));
}

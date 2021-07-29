<?php declare(strict_types=1);

namespace Kiboko\Component\SatelliteToolbox\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class ImportConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('imports');

        $builder->getRootNode()
            ->validate()
                ->ifEmpty()
                ->thenInvalid('Your imports configuration cannot be empty.')
            ->end()
            ->arrayPrototype()
                ->children()
                    ->variableNode('resource')
                        ->beforeNormalization()
                            ->ifString()
                            ->then(fn (string $data) => new ImportWorker($data))
                        ->end()
                        ->validate()
                            ->ifTrue(isExpression())
                            ->then(asExpression())
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $builder;
    }
}

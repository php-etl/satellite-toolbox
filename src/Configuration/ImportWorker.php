<?php declare(strict_types=1);

namespace Kiboko\Component\SatelliteToolbox\Configuration;

use Symfony\Component\Config\Loader\LoaderInterface;

final class ImportWorker
{
    public function __construct(private string $file)
    {
    }

    public function __invoke(LoaderInterface $loader)
    {
        return $loader->load($this->file);
    }
}

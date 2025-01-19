<?php 
namespace Enetpartner\Vslinkcomposer;

use Enetpartner\Vslinkcomposer\DependencyInjection\VslinkcomposerExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class VslinkcomposerBundle extends Bundle
{
    public function getContainerExtension(): VslinkcomposerExtension
    {
        return new VslinkcomposerExtension();
    }

    public function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(__DIR__ . '/Controller/', 'annotation');
    }
}
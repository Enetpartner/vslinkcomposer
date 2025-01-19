<?php 
namespace Enetpartner\Vslinkcomposer;

use Enetpartner\Vslinkcomposer\DependencyInjection\VslinkcomposerExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class FOSCKEditorBundle extends Bundle
{
    public function getContainerExtension(): VslinkcomposerExtension
    {
        return new VslinkcomposerExtension();
    }
}
<?php
namespace Enetpartner\Vslinkcomposer\Service;  
use Symfony\Component\DependencyInjection\ContainerInterface;

class AppService {
    
    public function __construct(private ContainerInterface $container)
    { 
        
    }
    
    public function getDepartement () {
        
        return $this->container->getParameter('app_departement');
    }   
    
    public function getParameter($code_parametre)
    {
        if ($this->container->hasParameter($code_parametre)) 
        {
            return $this->container->getParameter($code_parametre);
        }

        return null;
    }
}
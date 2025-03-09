<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * Override to handle database connection issues during boot
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        try {
            parent::boot();
        } catch (\Exception $e) {
            // Log the error
            error_log('Error during boot: ' . $e->getMessage());
            
            // If we're in production and this is a database error, we'll still boot
            // This allows the application to start even if the database is not available
            if ($this->environment === 'prod' && 
                (strpos($e->getMessage(), 'database') !== false || 
                 strpos($e->getMessage(), 'Database') !== false ||
                 strpos($e->getMessage(), 'SQLSTATE') !== false)) {
                
                $this->booted = true;
                return;
            }
            
            // For other errors, rethrow
            throw $e;
        }
    }
}

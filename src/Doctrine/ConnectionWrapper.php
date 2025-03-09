<?php

namespace App\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;

/**
 * A wrapper for Doctrine connections that gracefully handles connection errors
 * during application boot.
 */
class ConnectionWrapper extends Connection
{
    /**
     * Flag to track if we've already logged a connection error
     */
    private bool $connectionErrorLogged = false;

    /**
     * {@inheritdoc}
     */
    public function connect(): bool
    {
        if ($this->isConnected()) {
            return true;
        }

        try {
            return parent::connect();
        } catch (Exception $e) {
            if (!$this->connectionErrorLogged) {
                // Log the error only once to avoid spamming the logs
                error_log('Database connection error: ' . $e->getMessage());
                $this->connectionErrorLogged = true;
            }
            
            // Return false instead of throwing an exception
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getWrappedConnection(): DriverConnection
    {
        try {
            return parent::getWrappedConnection();
        } catch (Exception $e) {
            if (!$this->connectionErrorLogged) {
                // Log the error only once to avoid spamming the logs
                error_log('Database connection error: ' . $e->getMessage());
                $this->connectionErrorLogged = true;
            }
            
            // During build, we'll use a memory SQLite connection
            // This should be handled by the when@build configuration
            throw $e;
        }
    }
} 
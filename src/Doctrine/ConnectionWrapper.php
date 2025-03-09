<?php

namespace App\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Exception;

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
            
            // Create a dummy connection that does nothing
            return new class implements DriverConnection {
                public function prepare($sql): \Doctrine\DBAL\Driver\Statement { return null; }
                public function query(string $sql): \Doctrine\DBAL\Driver\Result { return null; }
                public function quote($value, $type = \PDO::PARAM_STR) { return "''"; }
                public function exec(string $sql): int { return 0; }
                public function lastInsertId($name = null) { return 0; }
                public function beginTransaction() { return true; }
                public function commit() { return true; }
                public function rollBack() { return true; }
            };
        }
    }
} 
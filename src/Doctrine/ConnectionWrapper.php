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
            
            // Create a dummy connection that properly implements the DriverConnection interface
            return new class implements DriverConnection {
                public function prepare(string $sql): Statement 
                { 
                    return new class implements Statement {
                        public function bindValue($param, $value, $type = ParameterType::STRING): void {}
                        public function bindParam($param, &$variable, $type = ParameterType::STRING, $length = null): void {}
                        public function execute($params = null): Result
                        {
                            return new class implements Result {
                                public function fetchNumeric() { return false; }
                                public function fetchAssociative() { return false; }
                                public function fetchOne() { return false; }
                                public function fetchAllNumeric(): array { return []; }
                                public function fetchAllAssociative(): array { return []; }
                                public function fetchFirstColumn(): array { return []; }
                                public function rowCount(): int { return 0; }
                                public function columnCount(): int { return 0; }
                                public function free(): void {}
                            };
                        }
                    };
                }
                
                public function query(string $sql): Result 
                { 
                    return new class implements Result {
                        public function fetchNumeric() { return false; }
                        public function fetchAssociative() { return false; }
                        public function fetchOne() { return false; }
                        public function fetchAllNumeric(): array { return []; }
                        public function fetchAllAssociative(): array { return []; }
                        public function fetchFirstColumn(): array { return []; }
                        public function rowCount(): int { return 0; }
                        public function columnCount(): int { return 0; }
                        public function free(): void {}
                    };
                }
                
                public function quote($value, $type = ParameterType::STRING): string { return "''"; }
                public function exec(string $sql): int { return 0; }
                public function lastInsertId($name = null): string { return "0"; }
                public function beginTransaction(): bool { return true; }
                public function commit(): bool { return true; }
                public function rollBack(): bool { return true; }
            };
        }
    }
} 
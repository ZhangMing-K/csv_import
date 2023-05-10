<?php

namespace App\Service;
use Doctrine\DBAL\Connection;

class BulkInsertService
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function bulkInsert(array $data)
    {
        $this->connection->beginTransaction();

        try {
            $batchSize = 1000; // number of records to insert at a time
            $count = count($data);
            $i = 0;
            $previousCount = $this->connection->executeQuery('SELECT COUNT(*) FROM csv_import')->fetchOne();

            while ($i < $count) {
                $batch = array_slice($data, $i, $batchSize);
                try {
                    $values = array();
                    $placeholders = array();
                    
                    foreach ($batch as $row) {
                        $values = array_merge($values, array_values($row));
                        $placeholders[] = '(' . implode(', ', array_fill(0, count($row), '?')) . ')';
                    }
                    
                    $sql = 'INSERT IGNORE INTO csv_import (url) VALUES ' . implode(', ', $placeholders);
                    
                    $this->connection->executeStatement($sql, $values);
                } catch (\Exception $e) {
                    echo 'Error code: ' . $e->getCode();
                }

                $i += $batchSize;
            }

            $newCount = $this->connection->executeQuery('SELECT COUNT(*) FROM csv_import')->fetchOne();
            $newlyAddedCount = $newCount - $previousCount;
            $this->connection->commit();
            return $newlyAddedCount;
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }
}
<?php
/**
 * Copyright © 2025 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Read and write access to the decline counters.
 *
 * Fixed window, not sliding. A sliding window is wrong for the BIN dimension: a popular issuer BIN
 * taking one honest decline every few minutes would never reset and would eventually lock out the
 * whole issuer.
 */
class PaymentAttempt
{
    public const TABLE_NAME = 'line_payment_attempt';

    private ResourceConnection $resource;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Decline counts for every given key, in one round trip.
     *
     * @param array<string, string> $keys dimension => value
     * @param int $storeId
     * @param string $cutoff UTC datetime; windows started before it are stale and read as zero
     *
     * @return array<string, int> dimension => declines
     */
    public function countsFor(array $keys, int $storeId, string $cutoff): array
    {
        if ($keys === []) {
            return [];
        }

        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->getTable(), ['dimension', 'dimension_value', 'declines'])
            ->where('store_id = ?', $storeId)
            ->where('window_started_at >= ?', $cutoff);

        $conditions = [];

        foreach ($keys as $dimension => $value) {
            $conditions[] = $connection->quoteInto(
                '(dimension = ? AND dimension_value = ' . $connection->quote($value) . ')',
                $dimension
            );
        }

        $select->where(implode(' OR ', $conditions));

        $counts = [];

        foreach ($connection->fetchAll($select) as $row) {
            $dimension = (string) $row['dimension'];

            if (($keys[$dimension] ?? null) === (string) $row['dimension_value']) {
                $counts[$dimension] = (int) $row['declines'];
            }
        }

        return $counts;
    }

    /**
     * Adds one decline to a counter, resetting it first if its window has expired.
     *
     * @param string $dimension
     * @param string $value
     * @param int $storeId
     * @param string $now UTC datetime
     * @param string $cutoff UTC datetime
     *
     * @return void
     */
    public function increment(
        string $dimension,
        string $value,
        int $storeId,
        string $now,
        string $cutoff
    ): void {
        $connection = $this->resource->getConnection();

        $sql = 'INSERT INTO ' . $connection->quoteIdentifier($this->getTable())
            . ' (dimension, dimension_value, store_id, declines, window_started_at, updated_at)'
            . ' VALUES (?, ?, ?, 1, ?, ?)'
            . ' ON DUPLICATE KEY UPDATE'
            . ' declines = IF(window_started_at < ?, 1, declines + 1),'
            . ' window_started_at = IF(window_started_at < ?, ?, window_started_at),'
            . ' updated_at = ?';

        $connection->query($sql, [
            $dimension,
            $value,
            $storeId,
            $now,
            $now,
            $cutoff,
            $cutoff,
            $now,
            $now
        ]);
    }

    /**
     * BINs with the most declines in the current window, for the circuit breaker log line.
     *
     * @param int $storeId
     * @param string $cutoff
     * @param int $limit
     *
     * @return array<string, int>
     */
    public function topBins(int $storeId, string $cutoff, int $limit = 3): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->getTable(), ['dimension_value', 'declines'])
            ->where('store_id = ?', $storeId)
            ->where('dimension = ?', 'bin')
            ->where('window_started_at >= ?', $cutoff)
            ->order('declines DESC')
            ->limit($limit);

        $result = [];

        foreach ($connection->fetchAll($select) as $row) {
            $result[(string) $row['dimension_value']] = (int) $row['declines'];
        }

        return $result;
    }

    /**
     * @param string $cutoff rows not touched since this datetime are dropped
     *
     * @return int
     */
    public function prune(string $cutoff): int
    {
        $connection = $this->resource->getConnection();

        return (int) $connection->delete(
            $this->getTable(),
            ['updated_at < ?' => $cutoff]
        );
    }

    /**
     * @return string
     */
    private function getTable(): string
    {
        return $this->resource->getTableName(self::TABLE_NAME);
    }
}

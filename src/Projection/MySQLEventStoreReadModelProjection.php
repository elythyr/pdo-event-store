<?php
/**
 * This file is part of the prooph/pdo-event-store.
 * (c) 2016-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2016-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\EventStore\PDO\Projection;

use PDO;
use Prooph\EventStore\PDO\MySQLEventStore;
use Prooph\EventStore\Projection\ReadModel;

final class MySQLEventStoreReadModelProjection extends AbstractPDOReadModelProjection
{
    use PDOEventStoreProjectionTrait;
    use PDOQueryTrait;

    /**
     * @var MySQLEventStore
     */
    protected $eventStore;

    /**
     * @var string
     */
    protected $projectionsTable;

    public function __construct(
        MySQLEventStore $eventStore,
        PDO $connection,
        string $name,
        ReadModel $readModel,
        string $eventStreamsTable,
        string $projectionsTable,
        int $lockTimeoutMs,
        int $cacheSize
    ) {
        parent::__construct(
            $eventStore,
            $connection,
            $name,
            $readModel,
            $eventStreamsTable,
            $projectionsTable,
            $lockTimeoutMs,
            $cacheSize
        );
    }

    public function delete(bool $deleteEmittedEvents): void
    {
        $this->connection->beginTransaction();

        $deleteProjectionSql = <<<EOT
DELETE FROM $this->projectionsTable WHERE name = ?;
EOT;
        $statement = $this->connection->prepare($deleteProjectionSql);
        $statement->execute([$this->name]);

        $this->connection->commit();

        parent::delete($deleteEmittedEvents);
    }
}
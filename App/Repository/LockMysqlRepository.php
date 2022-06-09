<?php

namespace App\Repository;

use App\Entity\Lock;

class LockMysqlRepository implements LockRepositoryInterface
{
    private string $table = 'lock';
    private \PDO $client;

    public function __construct(\PDO $client)
    {
        $this->client = $client;
    }

    public function add(Lock $lock): int
    {
        $sql = "INSERT INTO `$this->table`
            VALUES (NULL, :passcode_id, :passcode, :name, :start_date, :end_date)";

        $this->client->prepare($sql)
            ->execute([
                'name' => $lock->getName(),
                'passcode_id' => $lock->getPasscodeId(),
                'passcode' => $lock->getPasscode(),
                'start_date' => $lock->getStartDate()->format('Y-m-d H:i:s'),
                'end_date' => $lock->getEndDate()->format('Y-m-d H:i:s'),
            ]);
        return $this->client->lastInsertId();
    }

    public function find($id): ?Lock
    {
        $statement = $this->client->prepare("SELECT * FROM `$this->table` WHERE id = ?");
        $statement->execute([$id]);
        $row = $statement->fetch();
        return $row ? $this->toEntity($row) : null;
    }

    public function update(Lock $lock): void
    {
        $sql = "UPDATE `$this->table`
            SET
                name = :name,
                passcode_id = :passcode_id,
                passcode = :passcode,
                start_date = :start_date,
                end_date = :end_date
            WHERE id = :id";

        $this->client->prepare($sql)
            ->execute([
                'id' => $lock->getId(),
                'name' => $lock->getName(),
                'passcode_id' => $lock->getPasscodeId(),
                'passcode' => $lock->getPasscode(),
                'start_date' => $lock->getStartDate()->format('Y-m-d H:i:s'),
                'end_date' => $lock->getEndDate()->format('Y-m-d H:i:s'),
            ]);
    }

    /**
     * @param array $params
     * @return Lock[]
     */
    public function findBy(array $params): array
    {
        $where = '';
        $values = [];
        foreach ($params as $field => $value) {
            $where .= "$field = :$field";
            $values[$field] = $value;
        }

        $sql = "SELECT * FROM `$this->table`";

        if ($where) {
            $sql .= " WHERE $where";
        }

        $statement = $this->client->prepare($sql);
        $statement->execute($values);
        $rows = $statement->fetchAll();
        return array_map([$this, 'toEntity'], $rows);
    }

    public function delete($id): void
    {
        $this->client->prepare("DELETE FROM `$this->table` WHERE id = ?")->execute([$id]);
    }

    private function toEntity(array $row): Lock
    {
        return (new Lock())
            ->setId($row['id'])
            ->setName($row['name'])
            ->setPasscodeId($row['passcode_id'])
            ->setPasscode($row['passcode'])
            ->setStartDate(new \DateTime($row['start_date']))
            ->setEndDate(new \DateTime($row['end_date']));
    }
}

<?php

namespace App\Repository;

use App\Entity\Room;

class RoomMysqlRepository implements RoomRepositoryInterface
{
    private string $table = 'room';
    private \PDO $client;

    public function __construct(\PDO $client)
    {
        $this->client = $client;
    }

    public function add(Room $room): int
    {
        $sql = "INSERT INTO $this->table
            VALUES (NULL, :lock_id, :number)";

        $this->client->prepare($sql)
            ->execute([
                'lock_id' => $room->getLockId(),
                'number' => $room->getNumber(),
            ]);
        return $this->client->lastInsertId();
    }

    public function find($id): ?Room
    {
        $statement = $this->client->prepare("SELECT * FROM $this->table WHERE id = ?");
        $statement->execute([$id]);
        $row = $statement->fetch();
        return $row ? $this->toEntity($row) : null;
    }

    public function update(Room $room): void
    {
        $sql = "UPDATE $this->table
            SET
                lock_id = :lock_id,
                number = :number
            WHERE id = :id";

        $this->client->prepare($sql)
            ->execute([
                'id' => $room->getId(),
                'lock_id' => $room->getLockId(),
                'number' => $room->getNumber(),
            ]);
    }

    /**
     * @param array $params
     * @return Room[]
     */
    public function findBy(array $params): array
    {
        $where = '';
        $values = [];
        foreach ($params as $field => $value) {
            $where .= "$field = :$field";
            $values[$field] = $value;
        }

        $sql = "SELECT * FROM $this->table";

        if ($where) {
            $sql .= " WHERE $where";
        }

        $statement = $this->client->prepare($sql);
        $statement->execute($values);
        $rows = $statement->fetchAll();
        return array_map([$this, 'toEntity'], $rows);
    }

    public function findByNumber(string $number): ?Room
    {
        $statement = $this->client->prepare("SELECT * FROM $this->table WHERE number = ? limit 1");
        $statement->execute([$number]);
        $row = $statement->fetch();
        return $row ? $this->toEntity($row) : null;
    }

    public function getMainRoom(): Room
    {
        $statement = $this->client->prepare("SELECT * FROM $this->table WHERE number = 'main'");
        $statement->execute();
        $row = $statement->fetch();
        return $this->toEntity($row);
    }

    public function getAll(): array
    {
        $statement = $this->client->prepare("SELECT * FROM $this->table");
        $statement->execute();
        $rows = $statement->fetchAll();
        return array_map([$this, 'toEntity'], $rows);
    }

    public function delete($id): void
    {
        $this->client->prepare("DELETE FROM $this->table WHERE id = ?")->execute([$id]);
    }

    private function toEntity(array $row): Room
    {
        return (new Room())
            ->setId($row['id'])
            ->setLockId($row['lock_id'])
            ->setNumber($row['number']);
    }
}

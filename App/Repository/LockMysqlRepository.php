<?php

namespace App\Repository;

use App\Entity\Lock;

class LockMysqlRepository implements LockRepositoryInterface
{
    private string $table = 'lock';
    private \PDO $client;
    private BookingRepositoryInterface $bookingRepository;
    private RoomRepositoryInterface $roomRepository;

    public function __construct(
        \PDO $client,
        BookingRepositoryInterface $bookingRepository,
        RoomRepositoryInterface $roomRepository
    ) {
        $this->client = $client;
        $this->bookingRepository = $bookingRepository;
        $this->roomRepository = $roomRepository;
    }

    public function add(Lock $lock): int
    {
        $sql = "INSERT INTO `$this->table`
            VALUES (
                NULL,
                :passcode_id,
                :passcode,
                :name,
                :booking_id,
                :room_id,
                :deleted
            )";

        $this->client->prepare($sql)
            ->execute([
                'passcode_id' => $lock->getPasscodeId(),
                'passcode' => $lock->getPasscode(),
                'name' => $lock->getName(),
                'booking_id' => $lock->getBooking()->getId(),
                'room_id' => $lock->getRoom()->getId(),
                'deleted' => $lock->getDeleted() ? 1 : 0,
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
                booking_id = :booking_id,
                room_id = :room_id,
                deleted = :deleted
            WHERE id = :id";

        $this->client->prepare($sql)
            ->execute([
                'id' => $lock->getId(),
                'name' => $lock->getName(),
                'passcode_id' => $lock->getPasscodeId(),
                'passcode' => $lock->getPasscode(),
                'booking_id' => $lock->getBooking()->getId(),
                'room_id' => $lock->getRoom()->getId(),
                'deleted' => $lock->getDeleted() ? 1 : 0,
            ]);
    }

    /**
     * @param array $params
     * @return Lock[]
     */
    public function findBy(array $params): array
    {
        $where = [];
        $values = [];
        foreach ($params as $field => $value) {
            $where[] = "$field = :$field";
            $values[$field] = $value;
        }

        $sql = "SELECT * FROM `$this->table`";

        if ($where) {
            $sql .= ' WHERE ' . implode(' and ', $where);
        }

        $statement = $this->client->prepare($sql);
        $statement->execute($values);
        $rows = $statement->fetchAll();
        return array_map([$this, 'toEntity'], $rows);
    }

    public function getExpired(): array
    {
        $statement = $this->client->prepare("select * from `lock` l where l.end_date < CURDATE() and l.deleted = 0");
        $statement->execute();
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
            ->setBooking($this->bookingRepository->find($row['booking_id']))
            ->setRoom($this->roomRepository->find($row['room_id']))
            ->setDeleted((bool) $row['deleted']);
    }
}

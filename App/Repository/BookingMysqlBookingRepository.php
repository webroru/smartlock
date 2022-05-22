<?php

namespace App\Repository;

use App\Entity\Booking;

class BookingMysqlBookingRepository implements BookingRepositoryInterface
{
    private \PDO $client;
    private LockRepositoryInterface $lockRepository;

    public function __construct(\PDO $client, LockRepositoryInterface $lockRepository)
    {
        $this->client = $client;
        $this->lockRepository = $lockRepository;
    }

    public function add(Booking $booking): string
    {
        $sql = 'INSERT INTO booking
            VALUES (NULL, :name, :check_in_date, :check_out_date, :email, :code, :order_id, :property, :lock_id)';

        $this->client->prepare($sql)
            ->execute([
                'name' => $booking->getName(),
                'check_in_date' => $booking->getCheckInDate()->format('Y-m-d H:i:s'),
                'check_out_date' => $booking->getCheckOutDate()->format('Y-m-d H:i:s'),
                'email' => $booking->getEmail(),
                'code' => $booking->getCode(),
                'order_id' => $booking->getOrderId(),
                'property' => $booking->getProperty(),
                'lock_id' => $booking->getLock()?->getId(),
            ]);
        return $this->client->lastInsertId();
    }

    public function find(string $id): ?Booking
    {
        $statement = $this->client->prepare('SELECT * FROM booking WHERE id = ?');
        $statement->execute([$id]);
        $row = $statement->fetch();
        return $row ? $this->toEntity($row) : null;
    }

    public function update(Booking $booking): void
    {
        $sql = 'UPDATE booking
            SET
                name = :name,
                check_in_date = :check_in_date,
                check_out_date = :check_out_date,
                email = :email,
                code = :code,
                order_id = :order_id,
                property = :property,
                lock_id = :lock_id
            WHERE id = :id';

        $this->client->prepare($sql)
            ->execute([
                'id' => $booking->getId(),
                'name' => $booking->getName(),
                'check_in_date' => $booking->getCheckInDate()->format('Y-m-d H:i:s'),
                'check_out_date' => $booking->getCheckOutDate()->format('Y-m-d H:i:s'),
                'email' => $booking->getEmail(),
                'code' => $booking->getCode(),
                'order_id' => $booking->getOrderId(),
                'property' => $booking->getProperty(),
                'lock_id' => $booking->getLock()?->getId(),
            ]);
    }

    public function findBy(array $params): array
    {
        $where = '';
        $values = [];
        foreach ($params as $field => $value) {
            $where .= "$field = :$field";
            $values[$field] = $value;
        }

        $sql = 'SELECT * FROM booking';

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
        $this->client->prepare('DELETE FROM booking WHERE id = ?')->execute([$id]);
    }

    public function getUnregisteredBookingsByDateRange(\DateTime $checkInDate): array
    {
        $sql = 'SELECT * FROM booking where check_in_date <= :check_in_date AND code IS NULL';
        $statement = $this->client->prepare($sql);
        $statement->execute(['check_in_date' => $checkInDate->format('Y-m-d H:i:s')]);
        $rows = $statement->fetchAll();
        return array_map([$this, 'toEntity'], $rows);
    }

    private function toEntity(array $row): Booking
    {
        $booking = (new Booking())
            ->setId($row['id'])
            ->setCheckInDate(new \DateTime($row['check_in_date']))
            ->setCheckOutDate(new \DateTime($row['check_out_date']))
            ->setEmail($row['email'])
            ->setName($row['name'])
            ->setCode($row['code'])
            ->setOrderId($row['order_id'])
            ->setProperty($row['property']);

        if ($row['lock_id']) {
            $booking->setLock($this->lockRepository->find($row['lock_id']));
        }

        return $booking;
    }
}

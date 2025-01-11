<?php

namespace App\Repository;

use App\Entity\Booking;
use PDO;

class BookingMysqlBookingRepository implements BookingRepositoryInterface
{
    private string $table = 'booking';
    private \PDO $client;
    private RoomRepositoryInterface $roomRepository;

    public function __construct(\PDO $client, RoomRepositoryInterface $roomRepository)
    {
        $this->client = $client;
        $this->roomRepository = $roomRepository;
        $this->client->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
    }

    public function add(Booking $booking): int
    {
        $sql = 'INSERT INTO booking
            VALUES (
                NULL,
                :name,
                :phone,
                :check_in_date,
                :check_out_date,
                :order_id,
                :property
            )';

        $this->client->prepare($sql)
            ->execute([
                'name' => $booking->getName(),
                'phone' => $booking->getPhone(),
                'check_in_date' => $booking->getCheckInDate()->format('Y-m-d H:i:s'),
                'check_out_date' => $booking->getCheckOutDate()->format('Y-m-d H:i:s'),
                'order_id' => $booking->getOrderId(),
                'property' => $booking->getProperty(),
            ]);

        $bookingId = $this->client->lastInsertId();

        foreach ($booking->getRooms() as $room) {
            $this->addRoom($room->getId(), $bookingId);
        }

        return $bookingId;
    }

    public function find(int $id): ?Booking
    {
        $statement = $this->client->prepare('SELECT * FROM booking WHERE id = ?');
        $statement->execute([$id]);
        $row = $statement->fetch();
        return $row ? $this->toEntity($row) : null;
    }

    public function update(Booking $booking): void
    {
        $sql = "UPDATE $this->table
            SET
                name = :name,
                phone = :phone,
                check_in_date = :check_in_date,
                check_out_date = :check_out_date,
                order_id = :order_id,
                property = :property
            WHERE id = :id";

        $this->client->prepare($sql)
            ->execute([
                'id' => $booking->getId(),
                'name' => $booking->getName(),
                'phone' => $booking->getPhone(),
                'check_in_date' => $booking->getCheckInDate()->format('Y-m-d H:i:s'),
                'check_out_date' => $booking->getCheckOutDate()->format('Y-m-d H:i:s'),
                'order_id' => $booking->getOrderId(),
                'property' => $booking->getProperty(),
            ]);

        $this->removeRooms($booking->getId());
        foreach ($booking->getRooms() as $room) {
            $this->addRoom($room->getId(), $booking->getId());
        }
    }

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

    public function delete($id): void
    {
        $this->client->prepare('DELETE FROM booking WHERE id = ?')->execute([$id]);
    }

    private function toEntity(array $row): Booking
    {
        return (new Booking())
            ->setId($row['id'])
            ->setCheckInDate(new \DateTime($row['check_in_date'], new \DateTimeZone('Europe/Prague')))
            ->setCheckOutDate(new \DateTime($row['check_out_date'], new \DateTimeZone('Europe/Prague')))
            ->setName($row['name'])
            ->setPhone($row['phone'])
            ->setOrderId($row['order_id'])
            ->setProperty($row['property'])
            ->setRooms($this->getRooms($row['id']))
        ;
    }

    private function addRoom(int $roomId, int $bookingId): void
    {
        $this->client->prepare('INSERT INTO booking_has_room VALUES (?, ?)')
            ->execute([$bookingId, $roomId]);
    }

    private function removeRooms(int $bookingId): void
    {
        $this->client->prepare('DELETE FROM booking_has_room WHERE booking_id = ?')
            ->execute([$bookingId]);
    }

    private function getRooms(int $bookingId): array
    {
        $statement = $this->client->prepare('SELECT room_id FROM booking_has_room WHERE booking_id = ?');
        $statement->execute([$bookingId]);
        $rows = $statement->fetchAll();
        return array_map(fn($row) => $this->roomRepository->find($row['room_id']), $rows);
    }
}

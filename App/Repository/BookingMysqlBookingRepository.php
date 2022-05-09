<?php

namespace App\Repository;

use App\Entity\Booking;

class BookingMysqlBookingRepository implements BookingRepositoryInterface
{
    private $client;

    public function __construct()
    {
        $host = getenv('MYSQL_HOST');
        $db = getenv('MYSQL_DB');
        $user = getenv('MYSQL_USER');
        $pass = getenv('MYSQL_PASS');
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            $this->client = new \PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public function add(Booking $booking): string
    {
        $sql = 'INSERT INTO booking
            VALUES (NULL, :name, :check_in_date, :check_out_date, :email, :code, :order_id)';

        $this->client->prepare($sql)
            ->execute([
                'name' => $booking->getName(),
                'check_in_date' => $booking->getCheckInDate()->format('Y-m-d H:i:s'),
                'check_out_date' => $booking->getCheckOutDate()->format('Y-m-d H:i:s'),
                'email' => $booking->getEmail(),
                'code' => $booking->getCode(),
                'order_id' => $booking->getOrderId(),
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
                order_id = :order_id
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
        return (new Booking())
            ->setId($row['id'])
            ->setCheckInDate(new \DateTime($row['check_in_date']))
            ->setCheckOutDate(new \DateTime($row['check_out_date']))
            ->setEmail($row['email'])
            ->setName($row['name'])
            ->setCode($row['code'])
            ->setOrderId($row['order_id']);
    }
}

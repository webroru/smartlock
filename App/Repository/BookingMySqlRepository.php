<?php

namespace App\Repository;

use App\Entity\Booking;
use Google\Cloud\Firestore\DocumentSnapshot;
use Google\Cloud\Firestore\FirestoreClient;
use PDO;

class BookingMySqlRepository implements RepositoryInterface
{
    private $client;

    public function __construct()
    {
        $host = getenv('MYSQL_HOST');
        $db = getenv('MYSQL_DB');
        $user = getenv('host1253209');
        $pass = getenv('hostlandshino');
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            $this->client = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public function add(Booking $booking): string
    {
        $this->client->prepare('INSERT INTO booking VALUES (NULL, :name, :check_in_date, :check_out_date, :email, :code, :order_id)')
            ->execute([
                'name' => $booking->getName(),
                'check_in_date' => $booking->getCheckInDate(),
                'check_out_date' => $booking->getCheckOutDate(),
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
    }

    public function findBy(array $params): array
    {
    }

    public function delete($id): void
    {
    }

    public function getUnregisteredBookingsByDateRange(\DateTime $checkInDate): array
    {
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

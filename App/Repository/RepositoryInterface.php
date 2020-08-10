<?php

namespace App\Repository;

use App\Entity\Booking;
use Google\Cloud\Firestore\DocumentSnapshot;
use Google\Cloud\Firestore\FirestoreClient;

interface RepositoryInterface
{
    public function add(Booking $booking): string;
    public function find(string $id): ?Booking;
    public function update(Booking $booking): void;
    public function findBy(array $params): array;
    public function delete($id): void;
    public function getUnregisteredBookingsByDateRange(\DateTime $checkInDate): array;
}

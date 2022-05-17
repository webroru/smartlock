<?php

namespace App\Repository;

use App\Entity\Booking;
use Google\Cloud\Firestore\DocumentSnapshot;
use Google\Cloud\Firestore\FirestoreClient;

class BookingFirestoreBookingRepository implements BookingRepositoryInterface
{
    private $firestore;
    private $collectionReference;

    public function __construct()
    {
        $this->firestore = new FirestoreClient();
        $this->collectionReference = $this->firestore->collection(Booking::class);
    }

    public function add(Booking $booking): string
    {
        return $this->collectionReference->add([
            'Name' => $booking->getName(),
            'CheckInDate' => $booking->getCheckInDate(),
            'CheckOutDate' => $booking->getCheckOutDate(),
            'Email' => $booking->getEmail(),
            'Code' => $booking->getCode(),
            'OrderId' => $booking->getOrderId(),
            'Property' => $booking->getProperty(),
        ])->id();
    }

    public function find(string $id): ?Booking
    {
        $document = $this->collectionReference->document($id)->snapshot();
        return $document->exists() ? $this->toEntity($document) : null;
    }

    public function update(Booking $booking): void
    {
        $this->collectionReference->document($booking->getId())->update([
            ['path' => 'Name', 'value' => $booking->getName()],
            ['path' => 'CheckInDate', 'value' => $booking->getCheckInDate()],
            ['path' => 'CheckOutDate', 'value' => $booking->getCheckOutDate()],
            ['path' => 'Email', 'value' => $booking->getEmail()],
            ['path' => 'Code', 'value' => $booking->getCode()],
            ['path' => 'OrderId', 'value' => $booking->getOrderId()],
            ['path' => 'Property', 'value' => $booking->getProperty()],
        ]);
    }

    public function findBy(array $params): array
    {
        foreach ($params as $field => $value) {
            $this->collectionReference->where($field, '=', $value);
        }
        $result = [];
        foreach ($this->collectionReference->documents() as $document) {
            $result[] = $this->toEntity($document);
        }
        return $result;
    }

    public function delete($id): void
    {
        $this->collectionReference->document($id)->delete();
    }

    public function getUnregisteredBookingsByDateRange(\DateTime $checkInDate): array
    {
        /** @var DocumentSnapshot[] $documents */
        $documents = $this->collectionReference
            ->where('CheckInDate', '<=', $checkInDate)
            ->where('Code', '=', null)->documents();

        $result = [];
        foreach ($documents as $document) {
            $result[] = $this->toEntity($document);
        }
        return $result;
    }

    private function toEntity(DocumentSnapshot $document): Booking
    {
        return (new Booking())
            ->setId($document->id())
            ->setCheckInDate(new \DateTime($document->get('CheckInDate')))
            ->setCheckOutDate(new \DateTime($document->get('CheckOutDate')))
            ->setEmail($document->get('Email'))
            ->setName($document->get('Name'))
            ->setCode($document->get('Code'))
            ->setOrderId($document->get('OrderId'))
            ->setProperty($document->get('Property'));
    }
}

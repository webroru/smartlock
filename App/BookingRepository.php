<?php

namespace App;

use Google\Cloud\Firestore\DocumentSnapshot;
use Google\Cloud\Firestore\FirestoreClient;

class BookingRepository
{
    //private $entityName;
    private $firestore;
    private $collectionReference;

    public function __construct()
    {
        //$this->entityName = $entityName;
        $this->firestore = new FirestoreClient();

        $this->collectionReference = $this->firestore->collection(Booking::class);
        //$documentReference = $collectionReference->document($userId);
        //$snapshot = $documentReference->snapshot();
    }

    public function add(Booking $booking): void
    {
        $this->collectionReference->add([
            'Name' => $booking->getName(),
            'CheckInDate' => $booking->getCheckInDate(),
            'CheckOutDate' => $booking->getCheckOutDate(),
            'Email' => $booking->getEmail(),
        ]);
    }

    public function update(Booking $booking): void
    {
        $this->collectionReference->document($booking->getId())->set([
            'Name' => $booking->getName(),
            'CheckInDate' => $booking->getCheckInDate(),
            'CheckOutDate' => $booking->getCheckOutDate(),
            'Email' => $booking->getEmail(),
        ]);
    }

    public function getUnregisteredBookingsByDateRange(\DateTime $checkInDate): array
    {
        /** @var DocumentSnapshot[] $documents */
        $documents = $this->collectionReference
            ->where('CheckInDate', '<=', $checkInDate->getTimestamp())
            ->where('Code', '=', null)->documents();

        $result = [];
        foreach ($documents as $document) {
            $result[] = (new Booking())
                ->setId($document->id())
                ->setCheckInDate($document->get('CheckInDate'))
                ->setCheckOutDate($document->get('CheckOutDate'))
                ->setEmail($document->get('Email'))
                ->setName($document->get('Name'));
        }
        return $result;
    }
}

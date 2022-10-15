<?php

declare(strict_types=1);

namespace tests\App\Unit\Repository;

use App\Entity\Room;
use App\Repository\RoomRepositoryInterface;
use tests\App\Unit\UnitTestCase;

class RoomRepositoryTest extends UnitTestCase
{
    private RoomRepositoryInterface $roomRepository;
    private Room $room;

    protected function setUp(): void
    {
        $this->roomRepository = $this->getContainer()->get(RoomRepositoryInterface::class);

        $this->room = (new Room())
            ->setLockId('123')
            ->setNumber('test');
    }

    public function testAdd()
    {
        $id = $this->roomRepository->add($this->room);
        $this->roomRepository->delete($id);
        $this->assertIsInt($id);
    }

    public function testUpdate()
    {
        $id = $this->roomRepository->add($this->room);
        $this->room->setId($id);
        $this->room->setLockId('321');
        $this->roomRepository->update($this->room);
        $room = $this->roomRepository->find($id);
        $this->roomRepository->delete($id);
        $this->assertEquals('321', $room->getLockId());
    }

    public function testFind()
    {
        $id = $this->roomRepository->add($this->room);
        $room = $this->roomRepository->find($id);
        $this->roomRepository->delete($id);
        $this->assertInstanceOf(Room::class, ($room));
    }

    public function testFindBy()
    {
        $id = $this->roomRepository->add($this->room);
        $rooms = $this->roomRepository->findBy(['id' => $id]);
        $this->roomRepository->delete($id);
        $room = $rooms[0];
        $this->assertIsArray($rooms);
        $this->assertInstanceOf(Room::class, ($room));
        $this->assertEquals($id, $room->getId());
    }

    public function testDelete()
    {
        $id = $this->roomRepository->add($this->room);
        $this->roomRepository->delete($id);
        $result = $this->roomRepository->find($id);
        $this->assertNull($result);
    }
}

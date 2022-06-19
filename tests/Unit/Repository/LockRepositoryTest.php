<?php

namespace tests\App\Unit\Repository;

use App\Entity\Lock;
use App\Repository\LockMysqlRepository;
use App\Repository\LockRepositoryInterface;
use tests\App\Unit\UnitTestCase;

class LockRepositoryTest extends UnitTestCase
{
    private Lock $lock;
    private LockRepositoryInterface $lockRepository;

    protected function setUp(): void
    {
        $this->lockRepository = $this->getContainer()->get(LockRepositoryInterface::class);

        $this->lock = (new Lock())
            ->setName('test')
            ->setPasscode('0000')
            ->setPasscodeId(42)
            ->setStartDate(new \DateTime())
            ->setEndDate(new \DateTime('+1 year'));
    }

    public function testAdd()
    {
        $id = $this->lockRepository->add($this->lock);
        $this->lockRepository->delete($id);
        $this->assertIsInt($id);
    }

    public function testUpdate()
    {
        $id = $this->lockRepository->add($this->lock);
        $this->lock->setId($id);
        $this->lock->setPasscode('1111');
        $this->lockRepository->update($this->lock);
        $lock = $this->lockRepository->find($id);
        $this->lockRepository->delete($id);
        $this->assertEquals('1111', $lock->getPasscode());
    }

    public function testFind()
    {
        $id = $this->lockRepository->add($this->lock);
        $lock = $this->lockRepository->find($id);
        $this->lockRepository->delete($id);
        $this->assertInstanceOf(Lock::class, ($lock));
    }

    public function testFindBy()
    {
        $id = $this->lockRepository->add($this->lock);
        $locks = $this->lockRepository->findBy(['id' => $id]);
        $this->lockRepository->delete($id);
        $lock = $locks[0];
        $this->assertIsArray($locks);
        $this->assertInstanceOf(Lock::class, ($lock));
        $this->assertEquals($id, $lock->getId());
    }

    public function testDelete()
    {
        $id = $this->lockRepository->add($this->lock);
        $this->lockRepository->delete($id);
        $result = $this->lockRepository->find($id);
        $this->assertNull($result);
    }
}

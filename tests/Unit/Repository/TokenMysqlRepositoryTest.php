<?php

namespace Unit\Repository;

use App\Entity\Token;
use App\Repository\TokenMysqlRepository;
use tests\App\Unit\UnitTestCase;

class TokenMysqlRepositoryTest extends UnitTestCase
{
    private TokenMysqlRepository $tokenRepository;
    private Token $token;

    protected function setUp(): void
    {
        $this->tokenRepository = $this->getContainer()->get(TokenMysqlRepository::class);

        $this->token = (new Token())
            ->setAccessToken('test')
            ->setRefreshToken('test')
            ->setExpirationTime(new \DateTime('+1 year'));
    }

    public function testAdd()
    {
        $id = $this->tokenRepository->add($this->token);
        $this->tokenRepository->delete($id);
        $this->assertIsInt($id);
    }

    public function testUpdate()
    {
        $id = $this->tokenRepository->add($this->token);
        $this->token->setId($id);
        $this->token->setRefreshToken('foo');
        $this->tokenRepository->update($this->token);
        $token = $this->tokenRepository->find($id);
        $this->tokenRepository->delete($id);
        $this->assertEquals('foo', $token->getRefreshToken());
    }

    public function testFind()
    {
        $id = $this->tokenRepository->add($this->token);
        $token = $this->tokenRepository->find($id);
        $this->tokenRepository->delete($id);
        $this->assertInstanceOf(Token::class, ($token));
    }

    public function testFindBy()
    {
        $id = $this->tokenRepository->add($this->token);
        $tokens = $this->tokenRepository->findBy(['id' => $id]);
        $this->tokenRepository->delete($id);
        $token = $tokens[0];
        $this->assertIsArray($tokens);
        $this->assertInstanceOf(Token::class, ($token));
        $this->assertEquals($id, $token->getId());
    }

    public function testDelete()
    {
        $id = $this->tokenRepository->add($this->token);
        $this->tokenRepository->delete($id);
        $result = $this->tokenRepository->find($id);
        $this->assertNull($result);
    }
}

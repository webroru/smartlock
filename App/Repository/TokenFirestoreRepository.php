<?php

namespace App\Repository;

use App\Entity\Token;
use Google\Cloud\Firestore\DocumentSnapshot;
use Google\Cloud\Firestore\FirestoreClient;

class TokenFirestoreRepository implements TokenRepositoryInterface
{
    private FirestoreClient $firestore;
    private $collectionReference;

    public function __construct(FirestoreClient $firestore)
    {
        $this->firestore = $firestore;
        $this->collectionReference = $this->firestore->collection(Token::class);
    }

    public function add(Token $token): string
    {
        return $this->collectionReference->add([
            'AccessToken' => $token->getAccessToken(),
            'RefreshToken' => $token->getRefreshToken(),
            'ExpirationTime' => $token->getExpirationTime(),
        ])->id();
    }

    public function find($id): ?Token
    {
        $document = $this->collectionReference->document($id)->snapshot();
        return $document->exists() ? $this->toEntity($document) : null;
    }

    public function update(Token $token): void
    {
        $this->collectionReference->document($token->getId())->update([
            ['path' => 'AccessToken', 'value' => $token->getAccessToken()],
            ['path' => 'RefreshToken', 'value' => $token->getRefreshToken()],
            ['path' => 'ExpirationTime', 'value' => $token->getExpirationTime()],
        ]);
    }

    /**
     * @param array $params
     * @return Token[]
     */
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

    private function toEntity(DocumentSnapshot $document): Token
    {
        return (new Token())
            ->setId($document->id())
            ->setAccessToken($document->get('AccessToken'))
            ->setRefreshToken($document->get('RefreshToken'))
            ->setExpirationTime((new \DateTime($document->get('ExpirationTime'))));
    }
}

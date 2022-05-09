<?php

namespace App\Repository;

use App\Entity\Token;

class TokenMysqlRepository implements TokenRepositoryInterface
{
    private string $table = 'token';
    private \PDO $client;

    public function __construct(\PDO $client)
    {
        $this->client = $client;
    }

    public function add(Token $token): int
    {
        $sql = "INSERT INTO $this->table
            VALUES (NULL, :access_token, :refresh_token, :expiration_time)";

        $this->client->prepare($sql)
            ->execute([
                'access_token' => $token->getAccessToken(),
                'refresh_token' => $token->getRefreshToken(),
                'expiration_time' => $token->getExpirationTime()->format('Y-m-d H:i:s'),
            ]);
        return $this->client->lastInsertId();
    }

    public function find($id): ?Token
    {
        $statement = $this->client->prepare("SELECT * FROM $this->table WHERE id = ?");
        $statement->execute([$id]);
        $row = $statement->fetch();
        return $row ? $this->toEntity($row) : null;
    }

    public function update(Token $token): void
    {
        $sql = "UPDATE $this->table
            SET
                access_token = :access_token,
                refresh_token = :refresh_token,
                expiration_time = :expiration_time
            WHERE id = :id";

        $this->client->prepare($sql)
            ->execute([
                'id' => $token->getId(),
                'access_token' => $token->getAccessToken(),
                'refresh_token' => $token->getRefreshToken(),
                'expiration_time' => $token->getExpirationTime()->format('Y-m-d H:i:s'),
            ]);
    }

    /**
     * @param array $params
     * @return Token[]
     */
    public function findBy(array $params): array
    {
        $where = '';
        $values = [];
        foreach ($params as $field => $value) {
            $where .= "$field = :$field";
            $values[$field] = $value;
        }

        $sql = "SELECT * FROM $this->table";

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
        $this->client->prepare("DELETE FROM $this->table WHERE id = ?")->execute([$id]);
    }

    private function toEntity(array $row): Token
    {
        return (new Token())
            ->setId($row['id'])
            ->setAccessToken($row['access_token'])
            ->setRefreshToken($row['refresh_token'])
            ->setExpirationTime(new \DateTime($row['expiration_time']));
    }
}

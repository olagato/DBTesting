<?php
class UserMapper
{
    protected $db;
    protected $map;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->map = new SplObjectStorage;
    }

    public function findById($id)
    {
        $this->map->rewind();

        while ($this->map->valid()) {
            if ($this->map->getInfo() == $id) {
                return $this->map->current();
            }

            $this->map->next();
        }

        $query = $this->db->prepare('SELECT username FROM user WHERE id = ?');
        $query->execute(array($id));
        $username = $query->fetchColumn();

        if (!$username) {
            throw new OutOfBoundsException(sprintf('User with id #%d does not exist', $id));
        }

        $user = new User;
        $user->setId($id);
        $user->setUsername($username);

        $this->map[$user] = $id;

        return $user;
    }

    public function insert(\User $user)
    {
        if (isset($this->map[$user])) {
            throw new Exception('Object has an ID, cannot insert');
        }

        $query = $this->db->prepare('INSERT INTO user (username) VALUES (?)');
        $query->execute(array($user->getUsername()));

        $user->setId($this->db->lastInsertId());
        $this->map[$user] = (int) $this->db->lastInsertId();
    }

    public function delete(\User $user) 
    {
        if (!isset($this->map[$user])) {
            throw new Exception('Object does not exist, cannot delete');
        }

        $query = $this->db->prepare('DELETE FROM user WHERE id = ?');
        $query->execute(array($user->getId()));

        unset($this->map[$user]);
    }

    public function update(\User $user)
    {
        if (!isset($this->map[$user])) {
            throw new Exception('Object does not exist, cannot delete');
        }

        $query = $this->db->prepare('UPDATE user SET username = ? WHERE id = ?');
        $query->execute(array($user->getUsername(), $user->getId()));

        $this->map[$user] = $user->getId();
    }
}

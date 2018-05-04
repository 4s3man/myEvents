<?php
/**
 * Created by PhpStorm.
 * User: Kuba
 * Date: 29.04.18
 * Time: 13:35
 */

namespace Repositiory;

use Doctrine\DBAL\Connection;

/**
 * Class UserRepository
 */
class UserRepository
{
    /**
     * @var \Doctrine\DBAL\Connection $db
     */
    protected $db;

    /**
     * userRepository constructor.
     * @param \Doctrine\DBAL\Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Insert data to database
     *
     * @param array $data
     */
    public function save($data)
    {
        if (isset($data['name'])) {
            $this->db->insert('si_tags', $data);
        }
    }
}

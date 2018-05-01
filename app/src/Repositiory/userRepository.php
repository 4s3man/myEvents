<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 29.04.18
 * Time: 13:35
 */

namespace Repositiory;

use Doctrine\DBAL\Connection;

class userRepository
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

    public function save($data)
    {
        if (isset($data['name'])) {
            $this->db->insert('si_tags', $data);
        }
    }
}

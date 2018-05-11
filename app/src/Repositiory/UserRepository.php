<?php
/**
 * Created by PhpStorm.
 * User: Kuba
 * Date: 29.04.18
 * Time: 13:35
 */

namespace Repositiory;

/**
 * Class UserRepository
 */
class UserRepository extends AbstractRepository
{
    /**
     * Prepare first query part
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function queryAll()
    {
        return $this->db->createQueryBuilder()
            ->select('u.username', 'u.email', 'u.password', 'u.id', 'u.first_name', 'u.last_name', 'u.user_role')
            ->from('users', 'u');
    }

    /**
     * Insert data to database
     *
     * @param array $data
     */
    public function save($data)
    {
        //TODO stworzyć url i id kalendarza, wpisać je i user id do tabeli users has calendars
        $this->db->insert('users', $data);
    }
}

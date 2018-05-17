<?php
/**
 * Created by PhpStorm.
 * User: Kuba
 * Date: 29.04.18
 * Time: 13:35
 */

namespace Repositiory;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;

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
     * @var null|\Repositiory\CalendarRepository
     */
    protected $calendarRepository = null;

    protected $mediaRepository = null;

    /**
     * userRepository constructor.
     *
     * @param \Doctrine\DBAL\Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
        $this->calendarRepository = new CalendarRepository($db);
        $this->mediaRepository = new MediaRepository($db);
    }

    /**
     * Prepare first query part
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function queryAll()
    {
        return $this->db->createQueryBuilder()
            ->select('u.username', 'u.email', 'u.password', 'u.id', 'u.first_name', 'u.last_name', 'u.user_role_id')
            ->from('user', 'u');
    }

    /**
     * Saves or updates values into DB
     *
     * @param  array $user
     *
     * @throws DBALException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function save($user)
    {
        //TODO stworzyć url i id kalendarza, wpisać je i user id do tabeli users has calendars
        $this->db->beginTransaction();

        try {
            if (isset($user['id']) && ctype_digit((string) $user['id'])) {
                $id = $user['id'];
                unset($user['id']);

                $this->db->update('user', $user, ['id' => $id]);
            } else {
                $this->db->insert('calendar', [ 'token' => uniqid() ]);
                $user['calendar_id'] = $this->db->lastInsertId();
                $this->db->insert('user', $user);
            }
            $this->db->commit() ;
        } catch (DBALException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get first free id fo user table
     *
     * @return int
     */
    public function getNextId()
    {
        return StaticRepositoryFunctions::getNextId($this->db, 'user');
    }

    /**
     * Find all values in column matching $value
     *
     * @param String $value  to be find for uniqueness
     * @param String $column name witch $value in it
     *
     * @return array
     */
    public function findForUniqueness($value, $column)
    {
        return StaticRepositoryFunctions::findForUniqueness($this->queryAll(), $value, $column);
    }
}

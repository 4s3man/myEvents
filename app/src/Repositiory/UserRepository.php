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
use Silex\Provider\SecurityServiceProvider;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

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
            ->select('u.login', 'u.email', 'u.password', 'u.id', 'u.first_name', 'u.last_name', 'u.create_time', 'u.role_id')
            ->from('user', 'u');
    }

    /**
     * Saves or updates values into DB
     *
     * @param array $user
     *
     * @throws DBALException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function save($user)
    {
        $this->db->beginTransaction();
        try {
            //            if (isset($user['id']) && ctype_digit((string) $user['id'])) {
            //                $id = $user['id'];
            //                unset($user['id']);
            //
            //                //TODO superUser
            //                $this->db->update('role', $user['role'], ['id' => $user['role_id']]);
            //                $this->db->update('user', $user, ['id' => $id]);
            //            } else {
            //
            //            }
                $this->db->insert('role', ['role' => $user['role']]);
                unset($user['role']);
                $user['role_id'] = $this->db->lastInsertId();
                $this->db->insert('user', $user);
            $this->db->commit();
        } catch (DBALException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Return user and his roles
     *
     * @param string $login
     *
     * @return array
     */
    public function loadUserByLogin($login)
    {
        try {
            $user = $this->getUserByLogin($login);
            if (!$user || !count($user)) {
                throw new UsernameNotFoundException(
                    sprintf('Username "%s" does not exist.', $login)
                );
            }
            $roles = $this->getRoleById($user['id']);
            if (!$roles || !count($roles)) {
                throw new UsernameNotFoundException(
                    sprintf('Username "%s" does not exist.', $login)
                );
            }

            return [
                'login' => $user['login'],
                'password' => $user['password'],
                'roles' => $roles,
            ];
        } catch (DBALException $exception) {
            throw new UsernameNotFoundException(
                sprintf('Username %s does not exist.', $login)
            );
        } catch (UsernameNotFoundException $exception) {
            throw $exception;
        }
    }

    /**
     * Get user by login
     *
     * @param string $login
     *
     * @return array|mixed
     */
    public function getUserByLogin($login)
    {
        try {
            $qb = $this->queryAll()->select('u.id', 'u.login', 'u.password')->where('login = :login')
                ->setParameter(':login', $login, \PDO::PARAM_STR);

            return $qb->execute()->fetch();
        } catch (DBALException $exception) {
            return [];
        }
    }

    /**
     * Get role by Id
     *
     * @param int $userId
     *
     * @return array
     */
    public function getRoleById($userId)
    {
        $roles = [];
        try {
            $qb = $this->db->createQueryBuilder();
            $qb->select('r.role')
                ->from('role', 'r')
                ->innerJoin('r', 'user', 'u', 'u.role_id = r.id')
                ->where('u.id = :userId')
                ->setParameter(':userId', $userId, \PDO::PARAM_INT);
            $result = $qb->execute()->fetchAll();
            if ($result) {
                $roles = array_column($result, 'role');
            }

            return $roles;
        } catch (DBALException $exception) {
            return $roles;
        }
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
        $qb = $this->queryAll()->where($column.' = :value')
            ->setParameter(':value', $value);

        return $qb->execute()->fetchAll();
    }
}

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
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Validator\Constraints\Interfaces\NotItselfUniquenessInterface;
use Validator\Constraints\Interfaces\UniquenessInterface;

/**
 * Class UserRepository
 */
class UserRepository extends AbstractRepository implements UniquenessInterface, NotItselfUniquenessInterface
{
    /**
     *
     * @var null|MediaRepository
     */
    private $mediaReposioty = null;

    private $userCalendarRepository = null;

    /**
     * UserRepository constructor.
     *
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        parent::__construct($db);
        $this->mediaReposioty = new MediaRepository($db);
        $this->userCalendarRepository = new UserCaledarRepository($db);
    }


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
     * Find one record by id
     * @param int $userId
     *
     * @return array|mixed
     */
    public function findOneById($userId)
    {
        $qb = $this->queryAll()->where('id = :userId')
            ->setParameter(':userId', $userId, \PDO::PARAM_INT);
        $result = $qb->execute()->fetch();

        return $result ? $result : [];
    }

    /**
     * Saves or updates values into DB
     *
     * @param array                 $user
     *
     * @param BCryptPasswordEncoder $encoder
     *
     * @throws DBALException
     */
    public function save($user, $encoder)
    {
        //todo posprzątać to i user data managera
        $this->db->beginTransaction();
        try {
            if (isset($user['id']) && ctype_digit((string) $user['id'])) {
                $id = $user['id'];
                unset($user['id']);

                if (null !== $user['new_password']) {
                    $user['password'] = $encoder->encodePassword($user['new_password'], '');
                }

                unset($user['role']);
                unset($user['new_password']);
                unset($user['old_password']);

                $this->db->update('user', $user, ['id' => $id]);
            } else {
                $this->db->insert('role', ['role' => $user['role']]);
                unset($user['role']);
                $user['role_id'] = $this->db->lastInsertId();
                $this->db->insert('user', $user);
            }
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
            $rawCalendarRoles = $this->getUserCalendarsByUserId($user['id']);

            return [
                'id' => $user['id'],
                'login' => $user['login'],
                'password' => $user['password'],
                'roles' => $roles,
                'userCalendars' => $this->makeCalendarIdRoleArray($rawCalendarRoles),
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
     * Finds one record by email
     *
     * @param string $email
     *
     * @return array|mixed
     */
    public function findOneByEmail($email)
    {
        $qb = $this->queryAll()->where('u.email = :email')
            ->setParameter(':email', $email, \PDO::PARAM_STR);
        $result = $qb->execute()->fetch();

        return $result ? $result : [];
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

    /**
     * Finds for uniqueness if value is not passed value
     *
     * @param string $value
     * @param string $column
     * @param string $itself
     *
     * @return array
     */
    public function findForNotItselfUniqueness($value, $column, $itself)
    {
        $qb = $this->queryAll()->where($column.' = :value')
            ->andWhere($column.' != :itself')
            ->setParameter(':value', $value)
            ->setParameter(':itself', $itself);

        return $qb->execute()->fetchAll();
    }

    //todo add delete user role
    /**
     * Delete user and its links
     * @param int $userId
     *
     * @throws DBALException
     */
    public function delete($userId)
    {
        $this->db->beginTransaction();

        try {
            $mediaIds = $this->getLinkedMediaIds($userId);
            $this->db->delete('user_media', ['user_id' => $userId]);
            $this->deleteLinkedMedia($mediaIds);

            $this->db->delete('user_calendars', ['user_id' => $userId]);
            $this->db->delete('user', ['id' => $userId]);

            $this->db->commit();
        } catch (DBALException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Delete media links for user
     * @param array $mediaIds
     *
     * @return mixed
     */
    private function deleteLinkedMedia($mediaIds)
    {
        $qb = $this->db->createQueryBuilder()
            ->delete('media')
            ->where('id in (:ids)')
            ->setParameter(':ids', $mediaIds, Connection::PARAM_INT_ARRAY);

        return $qb->execute();
    }

    /**
     * Get linked media ids from user
     *
     * @param int $userId
     *
     * @return array
     */
    private function getLinkedMediaIds($userId)
    {
        $qb = $this->mediaReposioty->queryUserMedia($userId)
            ->select('uM.id');
        $result = $qb->execute()->fetchAll();
        $result = array_column($result, 'id');

        return $result ? $result : [];
    }

    /**
     * Get user calendars by user id
     * @param int $userId
     *
     * @return array
     */
    private function getUserCalendarsByUserId($userId)
    {
        $qb = $this->userCalendarRepository->queryAll()
            ->where('user_id = :userId')
            ->setParameter(':userId', $userId, \PDO::PARAM_INT);
        $result = $qb->execute()->fetchAll();

        return $result? $result : [];
    }

    /**
     * Makes Array key => calendar_id val => user_role
     * @param array $rawUserCalendars
     *
     * @return array
     */
    private function makeCalendarIdRoleArray(array $rawUserCalendars)
    {
        $userCalendars = [];
        foreach ($rawUserCalendars as $link) {
            $userCalendars[$link['calendar_id']] = $link['user_role'];
        }

        return $userCalendars;
    }
}

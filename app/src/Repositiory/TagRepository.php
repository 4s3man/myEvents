<?php /** @noinspection PhpCSValidationInspection */

/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 16.05.18
 * Time: 00:25
 */

namespace Repositiory;

use Doctrine\DBAL\Connection;

/**
 * Class CalendarRepository
 */
class TagRepository extends AbstractRepository
{
    /**
     * Query all from calendar
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function queryAll()
    {
        $query = $this->db->createQueryBuilder();

        return $query->select('t.id', 't.name')->from('tags', 't');
    }

    /**
     * Find record by name
     *
     * @param string $name
     *
     * @return array|mixed
     */
    public function findOneByName($name)
    {
        $qb = $this->queryAll()->where('t.name = :name')
            ->setParameter(':name', $name, \PDO::PARAM_STR);
        $result = $qb->execute()->fetch();

        return $result ? $result : [];
    }

    /**
     * Save Tag
     *
     * @param mixed $tag array[id,name] |null
     *
     * @return int
     */
    public function save($tag)
    {
        if (isset($tag['id']) && ctype_digit((string) $tag['id'])) {
            $id = $tag['id'];
            unset($tag['id']);

            return $this->db->update('tags', $tag, ['id' => $id]);
        } else {
            $this->db->insert('tags', $tag);
            $tag['id'] = $this->db->lastInsertId();

            return $tag;
        }
    }
}

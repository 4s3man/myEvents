<?php
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
class MediaRepository extends AbstractRepository
{
    /**
     * Query all from calendar
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function queryAll()
    {
        $query = $this->db->createQueryBuilder();

        return $query->select('m.id', 'm.title', 'm.img', 'm.width', 'm.height')->from('media', 'm');
    }
}

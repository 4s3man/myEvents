<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 16.05.18
 * Time: 00:25
 */

namespace Repositiory;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;

/**
 * Class CalendarRepository
 */
class MediaRepository extends AbstractRepository
{
    /**
     * @var Connection|null
     */
    protected $db = null;

    protected $tagsRepository = null;

    /**
     * MediaRepository constructor.
     *
     * @param Connection $db
     *
     * @param null       $userId
     *
     * @param null       $calendarId
     */
    public function __construct(Connection $db, $userId = null, $calendarId = null)
    {
        parent::__construct($db);
        $this->tagsRepository = new TagRepository($db);
    }


    /**
     * Query all from calendar
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function queryAll()
    {
        $qb = $this->db->createQueryBuilder();
        $qb->select('m.id', 'm.title', 'm.photo')->from('media', 'm');

        return $qb;
    }

    /**
     * @param array    $photo
     *
     * @param int      $userId
     * @param int|null $calnedarId
     *
     * @throws DBALException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function save($photo, $userId, $calnedarId = null)
    {
        $this->db->beginTransaction();
        try {
            if (isset($photo['id']) && ctype_digit((string) $photo['id'])) {
                $id = $photo['id'];
                unset($photo['id']);

                $this->db->update('media', $photo, ['id' => $id]);
            } else {
                $this->db->insert('media', $photo);
                $mediaId = $this->db->lastInsertId();
                $this->linkMediaToUser($userId, $mediaId);
            }
            $this->db->commit();
        } catch (DBALException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Links tables media and user via id in table user_media
     *
     * @param int $userId
     *
     * @param int $mediaId
     */
    private function linkMediaToUser($userId, $mediaId)
    {
        $this->db->insert(
            'user_media',
            [
               'user_id' => $userId,
               'media_id' => $mediaId,
            ]
        );
    }
}

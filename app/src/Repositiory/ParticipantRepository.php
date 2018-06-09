<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 08.06.18
 * Time: 15:29
 */

namespace Repositiory;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Validator\Constraints\Interfaces\UniquenessInterface;

/**
 * Class ParticipantRepository
 */
class ParticipantRepository extends AbstractRepository implements UniquenessInterface
{
    /**
     *
     * @var null|EventRepository
     */
    protected $eventRepository = null;

    /**
     * ParticipantRepository constructor.
     *
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        parent::__construct($db);
        $this->eventRepository = new EventRepository($db);
    }

    //TODO z rejestracją i partycypowaniem zrobić tak żeby user wysyłał email i wtedy następowało potwierdzenie

    /**
     *
     * @param array $participant
     * @param array $event
     *
     * @throws DBALException
     */
    public function save($participant, array $event)
    {
        $this->db->beginTransaction();
        $participant['event_id'] = $event['id'];
        try {
            $event['seats'] -= 1;
            if (isset($event['seats']) && 0 < $event['seats']) {
                $this->eventRepository->updateEvent($event);
                $this->db->insert('participant', $participant);
                $this->db->commit();
            }
        } catch (DBALException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Query all from participant table
     *
     * @return mixed
     */
    public function queryAll()
    {
        $qb = $this->db->createQueryBuilder();
        $qb->select('p.first_name', 'p.last_name', 'p.email', 'p.event_id')->from('participant', 'p');

        return $qb;
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

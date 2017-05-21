<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Queue;
use Doctrine\ORM\EntityRepository;

class QueueRepository extends EntityRepository
{
    public function delete(Queue $queue)
    {
        $this->_em->remove($queue);
        $this->_em->flush();
    }
}

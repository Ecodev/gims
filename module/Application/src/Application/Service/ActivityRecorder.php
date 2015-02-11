<?php

namespace Application\Service;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;

/**
 * Automatically records activity for all object which are recordable
 */
class ActivityRecorder implements EventSubscriber
{

    /**
     * @var array activities to be saved
     */
    private $activities = [];

    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
            Events::postFlush,
        ];
    }

    /**
     * Create a Activity object and queue it to be saved later
     * @param \Doctrine\ORM\UnitOfWork $unitOfWork
     * @param \Application\Model\AbstractRecordableActivity $entity
     * @param string $action
     */
    private function record(\Doctrine\ORM\UnitOfWork $unitOfWork, $entity, $action)
    {
        if (!$entity instanceof \Application\Model\AbstractRecordableActivity) {
            return;
        }

        $changes = $unitOfWork->getEntityChangeSet($entity);

        $activity = new \Application\Model\Activity();
        $activity->setRecord($entity);
        $activity->setAction($action);
        $activity->setChanges($changes);

        $this->activities[] = $activity;
    }

    /**
     * Records all action on recordable objects
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $unitOfWork = $eventArgs->getEntityManager()->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            $this->record($unitOfWork, $entity, 'create');
        }

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            $this->record($unitOfWork, $entity, 'update');
        }

        foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
            $this->record($unitOfWork, $entity, 'delete');
        }
    }

    /**
     * When everything is finished, we save our activies, if any
     * @param PostFlushEventArgs $eventArgs
     */
    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        if ($this->activities) {
            foreach ($this->activities as $a) {
                $a->extractRecord();
                $eventArgs->getEntityManager()->persist($a);
            }
            $this->activities = [];
            $eventArgs->getEntityManager()->flush();
        }
    }
}

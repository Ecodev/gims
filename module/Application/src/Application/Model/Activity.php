<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Activity is a log of important things that happened in the system
 * @ORM\Entity(repositoryClass="Application\Repository\ActivityRepository")
 */
class Activity extends AbstractModel
{

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    private $action;

    /**
     * An array of data that can be used to create a meaningful
     * message to end-user. Those data must be self-contained, because objects
     * may have been deleted.
     * @var array
     * @ORM\Column(type="json_array", nullable=false, options={"default" = "{}"})
     */
    private $data;

    /**
     * @var integer
     * @ORM\Column(type="smallint", nullable=false)
     */
    private $recordId;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    private $recordType;

    /**
     * An array of changed properties
     * @var array
     * @ORM\Column(type="json_array", nullable=false, options={"default" = "{}"})
     */
    private $changes = array();

    /**
     * @var AbstractRecordableActivity
     */
    private $record = null;

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), array(
            'action',
            'data',
            'changes',
        ));
    }

    /**
     * Set action
     * @param string $action
     * @return self
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set data
     * @param array $data
     * @return self
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set recordid
     * @param integer $recordid
     * @return self
     */
    private function setRecordId($recordid)
    {
        $this->recordId = $recordid;

        return $this;
    }

    /**
     * Get record id
     * @return integer
     */
    public function getRecordId()
    {
        return (int) $this->recordId;
    }

    /**
     * Set record type
     * @param string $recordType
     * @return self
     */
    private function setRecordType($recordType)
    {
        $this->recordType = $recordType;

        return $this;
    }

    /**
     * Get record type
     * @return string
     */
    public function getRecordType()
    {
        return $this->recordType;
    }

    /**
     * Set changed properties
     * @param array $changes
     * @return self
     */
    public function setChanges(array $changes)
    {
        $forbidden = [
            'dateCreated',
            'dateModified',
            'creator',
            'modifier',
        ];

        // Filter out common properties and objects
        $clean = [];
        foreach ($changes as $key => $change) {

            if (in_array($key, $forbidden)) {
                continue;
            }

            if (is_object($change[0]) || is_object($change[1])) {
                continue;
            }

            // Avoid saving loosely equal things, such as "1.00" and 1, since it doesn't actually change
            if ($change[0] == $change[1]) {
                continue;
            }

            $clean[$key] = $change;
        }

        $this->changes = $clean;

        return $this;
    }

    /**
     * Get changed properties
     * @return array
     */
    public function getChanges()
    {
        return $this->changes;
    }

    /**
     * Set record
     * @param AbstractRecordableActivity $record
     * @return self
     */
    public function setRecord(AbstractRecordableActivity $record)
    {
        $this->record = $record;
        $this->extractRecord();

        return $this;
    }

    /**
     * Extract info from currently set record
     * @return self
     */
    public function extractRecord()
    {
        if ($this->record->getId()) {
            $this->setRecordId($this->record->getId());
            $this->setRecordType(lcfirst(\Application\Utility::getShortClassName($this->record)));
            $this->setData($this->record->getActivityData());
        }

        return $this;
    }

}

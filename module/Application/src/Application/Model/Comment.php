<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * A comment is a part of a discussion
 * @ORM\Entity(repositoryClass="Application\Repository\CommentRepository")
 */
class Comment extends AbstractModel
{

    /**
     * @var Survey
     * @ORM\ManyToOne(targetEntity="Application\Model\Discussion", inversedBy="comments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $discussion;

    /**
     * @var string
     * @ORM\Column(type="string", length=4096, nullable=true)
     */
    private $description;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $attachmentName;

    /**
     * {@inheritdoc}
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), [
            'description',
        ]);
    }

    /**
     * Set discussion
     *
     * @param Discussion $discussion
     *
     * @return self
     */
    public function setDiscussion(Discussion $discussion)
    {
        $this->discussion = $discussion;
        $this->discussion->commentAdded($this);

        return $this;
    }

    /**
     * Get discussion
     *
     * @return Discussion
     */
    public function getDiscussion()
    {
        return $this->discussion;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set attachmentName
     *
     * @return self
     */
    public function setAttachmentName($attachmentName)
    {
        $this->attachmentName = $attachmentName;

        return $this;
    }

    /**
     * Get attachmentName
     *
     * @return string
     */
    public function getAttachmentName()
    {
        return $this->attachmentName;
    }
}

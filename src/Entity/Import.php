<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ImportRepository;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ImportRepository::class)]
class Import extends AbstractEntity {
    #[ORM\ManyToOne(targetEntity: 'Podcast', inversedBy: 'imports')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Podcast $podcast = null;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\Url(normalizer: 'trim', protocols: ['http', 'https'])]
    private ?string $rss = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(type: 'text')]
    private ?string $message = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $progress = null;

    private static $pendingStatus = 'PENDING';

    private static $workingStatus = 'WORKING';

    private static $successStatus = 'SUCCESS';

    private static $failureStatus = 'FAILURE';

    public function __toString() : string {
        return "{$this->getId()}";
    }

    /**
     * @return array<string>
     */
    public static function getActiveStatuses() : array {
        return [self::$pendingStatus, self::$workingStatus];
    }

    /**
     * @return array<string>
     */
    public static function getFinishedStatuses() : array {
        return [self::$successStatus, self::$failureStatus];
    }

    public function getPodcast() : ?Podcast {
        return $this->podcast;
    }

    public function setPodcast(?Podcast $podcast) : self {
        $this->podcast = $podcast;

        return $this;
    }

    public function getRss() : ?string {
        return $this->rss;
    }

    public function setRss(string $rss) : self {
        $this->rss = $rss;

        return $this;
    }

    public function getStatus() : ?string {
        return $this->status;
    }

    public function isActive() : ?bool {
        return in_array($this->status, self::getActiveStatuses(), true);
    }

    public function isFinished() : ?bool {
        return in_array($this->status, self::getFinishedStatuses(), true);
    }

    public function isSuccess() : ?bool {
        return $this->status === self::$successStatus;
    }

    public function setPendingStatus() : self {
        $this->status = self::$pendingStatus;

        return $this;
    }

    public function setWorkingStatus() : self {
        $this->status = self::$workingStatus;

        return $this;
    }

    public function setSuccessStatus() : self {
        $this->status = self::$successStatus;

        return $this;
    }

    public function setFailureStatus() : self {
        $this->status = self::$failureStatus;

        return $this;
    }

    public function getMessage() : ?string {
        return $this->message;
    }

    public function setMessage(?string $message) : self {
        $this->message = $message;

        return $this;
    }

    public function getProgress() : ?int {
        return $this->progress;
    }

    public function setProgress(?int $progress) : self {
        $this->progress = $progress;

        return $this;
    }
}

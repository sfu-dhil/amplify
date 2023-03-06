<?php

namespace App\Entity;

use App\Repository\ExportRepository;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass=ExportRepository::class)
 */
class Export extends AbstractEntity {
    private static $pendingStatus = 'PENDING';
    private static $workingStatus = 'WORKING';
    private static $successStatus = 'SUCCESS';
    private static $failureStatus = 'FAILURE';

    /**
     * @var Season
     * @ORM\ManyToOne(targetEntity="Season", inversedBy="exports")
     * @ORM\JoinColumn(nullable=false)
     */
    private $season;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $message;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $format;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $path = null;

    /**
     * {@inheritdoc}
     */
    public function __toString() : string {
        return $this->id;
    }


    public function getSeason(): ?Season {
        return $this->season;
    }

    public function setSeason(?Season $season): self {
        $this->season = $season;

        return $this;
    }

    public function getStatus(): ?string {
        return $this->status;
    }

    public function isActive(): ?bool {
        return in_array($this->status, self::getActiveStatuses());
    }

    public function isFinished(): ?bool {
        return in_array($this->status, self::getFinishedStatuses());
    }

    public function isSuccess(): ?bool {
        return $this->status === self::$successStatus;
    }

    public function setPendingStatus(): self {
        $this->status = self::$pendingStatus;

        return $this;
    }

    public function setWorkingStatus(): self {
        $this->status = self::$workingStatus;

        return $this;
    }

    public function setSuccessStatus(): self {
        $this->status = self::$successStatus;

        return $this;
    }

    public function setFailureStatus(): self {
        $this->status = self::$failureStatus;

        return $this;
    }

    public function getMessage(): ?string {
        return $this->message;
    }

    public function setMessage(?string $message): self {
        $this->message = $message;

        return $this;
    }

    public function getFormat(): ?string {
        return $this->format;
    }

    public function setFormat(string $format): self {
        $this->format = $format;

        return $this;
    }

    public function getPath() : ?string {
        return $this->path;
    }

    public function setPath(?string $path) : self {
        $this->path = $path;

        return $this;
    }

    /**
     * @return Array<string>
     */
    public static function getActiveStatuses() : array {
        return [self::$pendingStatus, self::$workingStatus];
    }

    /**
     * @return Array<string>
     */
    public static function getFinishedStatuses() : array {
        return [self::$successStatus, self::$failureStatus];
    }
}

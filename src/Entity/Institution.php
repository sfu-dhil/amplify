<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InstitutionRepository;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass=InstitutionRepository::class)
 * @ORM\Table(uniqueConstraints={
 *   @ORM\UniqueConstraint(name="institutions_uniq", columns={"province", "name"})
 * })
 */
class Institution extends AbstractEntity {

    /**
     * @var string
     * @ORM\Column(type="string", length=64, nullable=false)
     */
    private $province;

    /**
     * @var string
     * @ORM\Column(type="string", length=200, nullable=false)
     */
    private $name;

    /**
     * @inheritDoc
     */
    public function __toString() : string {
        return $this->name;
    }

    public function __construct() {
        parent::__construct();
    }

    public function getProvince(): ?string
    {
        return $this->province;
    }

    public function setProvince(string $province): self
    {
        $this->province = $province;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

}

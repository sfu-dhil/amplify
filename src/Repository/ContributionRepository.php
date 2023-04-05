<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contribution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Contribution find($id, $lockMode = null, $lockVersion = null)
 * @method Contribution[] findAll()
 * @method Contribution[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Contribution findOneBy(array $criteria, array $orderBy = null)
 *
 * @phpstan-extends ServiceEntityRepository<Contribution>
 */
class ContributionRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Contribution::class);
    }
}

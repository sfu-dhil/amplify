<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Import;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Import>
 *
 * @method null|Import find($id, $lockMode = null, $lockVersion = null)
 * @method null|Import findOneBy(array $criteria, array $orderBy = null)
 * @method Import[] findAll()
 * @method Import[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImportRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Import::class);
    }
}

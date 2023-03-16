<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Export;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Export>
 *
 * @method null|Export find($id, $lockMode = null, $lockVersion = null)
 * @method null|Export findOneBy(array $criteria, array $orderBy = null)
 * @method Export[] findAll()
 * @method Export[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExportRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Export::class);
    }
}

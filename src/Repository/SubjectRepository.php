<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Repository;

use App\Entity\Subject;
use Doctrine\Persistence\ManagerRegistry;
use Nines\UtilBundle\Repository\TermRepository;

/**
 * @method null|Subject find($id, $lockMode = null, $lockVersion = null)
 * @method null|Subject findOneBy(array $criteria, array $orderBy = null)
 * @method Subject[]    findAll()
 * @method Subject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubjectRepository extends TermRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Subject::class);
    }
}

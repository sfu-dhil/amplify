<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:import:categories')]
class ImportCategoriesCommand extends Command {
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
        parent::__construct(null);
    }

    protected function configure() : void {
        $this->setDescription('Import categories from a CSV file.');
        $this->addArgument('file', InputArgument::REQUIRED, 'A CSV file to import');
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $file = $input->getArgument('file');
        $csv = Reader::createFromPath($file, 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv->getRecords() as $record) {
            $category = new Category();
            if ($record['Secondary']) {
                $category->setLabel(implode(' - ', $record));
            } else {
                $category->setLabel($record['Primary']);
            }
            $category->setName(preg_replace('/[^a-z]+/', '_', mb_convert_case($category->getLabel(), MB_CASE_LOWER)));
            $this->em->persist($category);
        }
        $this->em->flush();

        return 0;
    }
}

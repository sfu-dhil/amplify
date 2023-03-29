<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Institution;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:import:institutions')]
class ImportInstitutionsCommand extends Command {
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
        parent::__construct(null);
    }

    protected function configure() : void {
        $this->setDescription('Import data');
        $this->addArgument('file', InputArgument::REQUIRED, 'CSV file to import.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $file = $input->getArgument('file');

        $csv = Reader::createFromPath($file, 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv->getRecords() as $record) {
            $institution = new Institution();
            $institution->setCountry($record['Country']);
            $institution->setName($record['Institution']);
            $this->em->persist($institution);
        }
        $this->em->flush();

        return 0;
    }
}

<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Language;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:import:languages')]
class ImportLanguagesCommand extends Command {
    private EntityManagerInterface $em;

    protected function configure() : void {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('file', InputArgument::REQUIRED, 'CSV file with language codes and names to import')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $path = $input->getArgument('file');
        $reader = Reader::createFromPath($path);
        foreach ($reader->getRecords() as $row) {
            $language = new Language();
            $language->setName($row[0]);
            $language->setLabel($row[1]);
            $this->em->persist($language);
        }
        $this->em->flush();

        return 0;
    }

    #[\Symfony\Contracts\Service\Attribute\Required]
    public function setEntityManager(EntityManagerInterface $em) : void {
        $this->em = $em;
    }
}

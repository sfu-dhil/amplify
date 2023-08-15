<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\ImportMessage;
use App\Repository\ImportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ImportHandler {
    public function __construct(
        private KernelInterface $kernel,
        private EntityManagerInterface $entityManager,
        private ImportRepository $importRepository,
        private LoggerInterface $messengerLogger,
    ) {
    }

    public function __invoke(ImportMessage $importMessage) : void {
        $import = $this->importRepository->find($importMessage->getImportId());

        try {
            $rss = $import->getRss();
            $podcastId = $import?->getPodcast()?->getId() ?? '';
            $userId = $import?->getUser()?->getId() ?? '';

            $this->messengerLogger->notice("Starting Import {$import->getId()} on RSS Feed {$rss} Podcast {$podcastId}");
            $import->setWorkingStatus();
            $import->setProgress(0);
            $import->setMessage('Importing Files');
            $this->entityManager->persist($import);
            $this->entityManager->flush();

            // setup the console command
            $application = new Application($this->kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput([
                'command' => 'app:import:podcast',
                'url' => $rss,
                'podcastId' => $podcastId,
                'importId' => $import->getId(),
                'userId' => $userId,
            ]);

            $output = new StreamOutput(fopen('php://stdout', 'w'));
            $success = $application->run($input, $output);

            if ($success) {
                $import->setSuccessStatus();
                $import->setMessage('Import complete!');
            } else {
                $import->setFailureStatus();
            }
            $import->setProgress(100);
            $this->entityManager->persist($import);
            $this->entityManager->flush();

            $this->messengerLogger->notice("Finished import {$import->getId()} on RSS Feed {$rss} Podcast {$podcastId}");
        } catch (Exception $e) {
            $import->setFailureStatus();
            $import->setMessage('There was a problem importing the podcast.');
            $this->entityManager->persist($import);
            $this->entityManager->flush();

            $this->messengerLogger->error("Error Import {$import->getId()} on RSS Feed {$rss} Podcast {$podcastId} \n{$e->getMessage()}");

            throw $e;
        }
    }
}

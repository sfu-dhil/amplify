<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\ImportMediaFixMessage;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ImportMediaFixHandler {
    public function __construct(
        private KernelInterface $kernel,
        private LoggerInterface $messengerLogger,
    ) {}

    public function __invoke(ImportMediaFixMessage $importMediaFixMessage) : void {
        try {
            $this->messengerLogger->notice('Starting Import Media Fix');

            // setup the console command
            $application = new Application($this->kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput([
                'command' => 'app:media:missing:download',
            ]);

            $output = new StreamOutput(fopen('php://stdout', 'w'));
            $application->run($input, $output);

            $this->messengerLogger->notice('Finished Import Media Fix');
        } catch (Exception $e) {
            $this->messengerLogger->error("Error Import Media Fix \n{$e->getMessage()}");

            throw $e;
        }
    }
}

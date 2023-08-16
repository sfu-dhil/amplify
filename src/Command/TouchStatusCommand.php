<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\PodcastRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:touch:status')]
class TouchStatusCommand extends Command {
    public function __construct(
        private EntityManagerInterface $em,
        private PodcastRepository $podcastRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $podcasts = $this->podcastRepository->findAll();

        foreach ($podcasts as $podcast) {
            $podcast->preUpdate();
            $this->em->persist($podcast);
            foreach ($podcast->getSeasons() as $season) {
                $season->preUpdate();
                $this->em->persist($season);
            }
            foreach ($podcast->getEpisodes() as $episode) {
                $episode->preUpdate();
                $this->em->persist($episode);
            }
            $this->em->flush();
        }

        return 1;
    }
}

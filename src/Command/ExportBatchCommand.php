<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Command;

use App\Entity\Episode;
use App\Repository\SeasonRepository;
use DOMDocument;
use Nines\MediaBundle\Service\AudioManager;
use Soundasleep\Html2Text;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

class ExportBatchCommand extends Command {
    private SeasonRepository $repository;

    private Environment $twig;

    private AudioManager $audioManager;

    protected static $defaultName = 'app:export:batch';

    protected static $defaultDescription = 'Export a season of a podcast for an islandora batch import.';

    protected function configure() : void {
        $this->setDescription(self::$defaultDescription)->addArgument('seasonId', InputArgument::REQUIRED, 'Season database ID')->addArgument('directory', InputArgument::REQUIRED, 'Directory to export to');
    }

    protected function generateMods($type, $object, $destination) : void {
        $mods = $this->twig->render("export/{$type}.xml.twig", ['object' => $object]);
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->loadXML($mods);
        $doc->save($destination);
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $io = new SymfonyStyle($input, $output);
        $season = $this->repository->find($input->getArgument('seasonId'));
        if ( ! $season) {
            $io->writeln('Cannot find season ' . $input->getArgument('seasonId'));

            return 1;
        }
        $dir = $input->getArgument('directory');
        $fs = new Filesystem();

        if ($fs->exists($dir)) {
            $output->writeln("Warning: export directory {$dir} already exists.");
            $fs->remove($dir);
        }
        $fs->mkdir($dir, 0755);

        foreach ($season->getEpisodes() as $episode) {
            $slug = $episode->getSlug();
            $path = "{$dir}/{$slug}";

            $fs->mkdir($path, 0755);
            $this->generateMods('episode', $episode, "{$path}/MODS.xml");

            $fs->mkdir("{$path}/episode", 0755);
            $this->generateMods('audio', $episode, "{$path}/episode/MODS.xml");

            $obj = $episode->getAudio('audio/x-wav');
            if ( ! $obj) {
                $obj = $episode->getAudio('audio/mpeg');
            }
            $mp3 = $episode->getAudio('audio/mpeg');

            $fs->copy($obj->getFile(), "{$path}/episode/OBJ." . $obj->getExtension());
            if ($mp3 && $mp3 !== $obj) {
                $fs->copy($mp3->getFile(), "{$path}/episode/PROXY_MP3." . $obj->getExtension());
            }

            if ($episode->getTranscript()) {
                $fs->mkdir("{$path}/transcript", 0755);
                $this->generateMods('transcript', $episode, "{$path}/transcript/MODS.xml");
                $text = Html2Text::convert($episode->getTranscript());
                $fs->dumpFile("{$path}/transcript/FULL_TEXT.txt", wordwrap($text));
                if (count($episode->getPdfs())) {
                    $fs->copy($episode->getPdfs()[0]->getFile(), "{$path}/transcript/OBJ.pdf");
                }
            }

            $images = array_merge($episode->getImages(), $episode->getSeason()->getImages(), $episode->getPodcast()->getImages());
            if (count($images)) {
                foreach ($images as $n => $image) {
                    $subdir = "{$path}/img_{$n}";
                    $fs->mkdir($subdir, 0755);
                    $this->generateMods('image', $image, "{$subdir}/MODS.xml");
                    $fs->copy($image->getFile()->getRealPath(), "{$subdir}/OBJ." . $image->getExtension());
                }
            }

            $this->generateStructure($episode, "{$path}/structure.xml");
        }

        return 0;
    }

    public function generateStructure(Episode $episode, $destination) : void {
        $slug = $episode->getSlug();

        $xml = '<?xml version="1.0" encoding="utf-8"?>';
        $xml .= "<islandora_compound_object title='{$slug}'>";
        $xml .= "<child content='{$slug}/episode'/>";

        $images = array_merge($episode->getImages(), $episode->getSeason()->getImages(), $episode->getPodcast()->getImages());
        foreach ($images as $n => $image) {
            $xml .= "<child content='{$slug}/img_{$n}'/>";
        }

        $xml .= "<child content='{$slug}/transcript'/>";
        $xml .= '</islandora_compound_object>';

        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->loadXML($xml);
        $doc->save($destination);
    }

    /**
     * @required
     */
    public function setSeasonRepository(SeasonRepository $repository) : void {
        $this->repository = $repository;
    }

    /**
     * @required
     */
    public function setTwig(Environment $twig) : void {
        $this->twig = $twig;
    }

    /**
     * @required
     */
    public function setAudioManager(AudioManager $audioManager) : void {
        $this->audioManager = $audioManager;
    }
}

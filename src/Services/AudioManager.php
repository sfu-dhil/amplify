<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Services;

use App\Entity\Audio;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Description of FileUploader.
 *
 * @author Michael Joyce <ubermichael@gmail.com>
 */
class AudioManager {
    public const FORBIDDEN = '/[^a-z0-9_. -]/i';

    /**
     * @var LoggerInterface
     */
    private $logger;

    private $root;

    /**
     * @var string
     */
    private $uploadDir;

    public function __construct(LoggerInterface $logger, $root) {
        $this->logger = $logger;
        $this->root = $root;
    }

    private function uploadFile(Audio $audio) : void {
        $file = $audio->getAudioFile();
        if ( ! $file instanceof UploadedFile) {
            return;
        }
        $audio->setOriginalName($file->getClientOriginalName());

        $filename = $this->upload($file);
        $path = $this->uploadDir . '/' . $filename;

        $audioFile = new File($path);
        $audio->setFileSize($audioFile->getSize());
        $audio->setAudioFile($audioFile);
        $audio->setAudioPath($filename);
        $audio->setMimeType($audioFile->getMimeType());
    }

    public function setUploadDir($dir) : void {
        if ('/' !== $dir[0]) {
            $this->uploadDir = $this->root . '/' . $dir;
        } else {
            $this->uploadDir = $dir;
        }
    }

    /**
     * @return string
     */
    public function getUploadDir() {
        return $this->uploadDir;
    }

    public function upload(UploadedFile $file) {
        $basename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $filename = implode('.', [
            preg_replace(self::FORBIDDEN, '_', $basename),
            uniqid(),
            $file->guessExtension(),
        ]);
        if ( ! file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
        $file->move($this->uploadDir, $filename);

        return $filename;
    }

    public function prePersist(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($entity instanceof Audio) {
            $this->uploadFile($entity);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args) : void {
        $entity = $args->getEntity();
        if ( ! $entity instanceof Audio) {
            return;
        }
        $this->uploadFile($entity);
    }

    public function postLoad(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($entity instanceof Audio) {
            $filePath = $this->uploadDir . '/' . $entity->getAudioPath();
            if (file_exists($filePath)) {
                $entity->setAudioFile(new File($filePath));
            }
        }
    }

    public function postRemove(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($entity instanceof Audio) {
            $fs = new Filesystem();

            try {
                $fs->remove($entity->getAudioFile());
            } catch (IOExceptionInterface $ex) {
                $this->logger->error("An error occured removing {$ex->getPath()}: {$ex->getMessage()}");
            }
        }
    }

    public function getMaxUploadSize($asBytes = true) {
        static $maxBytes = -1;

        if ($maxBytes < 0) {
            $postMax = $this->parseSize(ini_get('post_max_size'));
            if ($postMax > 0) {
                $maxBytes = $postMax;
            }

            $uploadMax = $this->parseSize(ini_get('upload_max_filesize'));
            if ($uploadMax > 0 && $uploadMax < $maxBytes) {
                $maxBytes = $uploadMax;
            }
        }
        if ($asBytes) {
            return $maxBytes;
        }
        $units = ['b', 'Kb', 'Mb', 'Gb', 'Tb'];
        $exp = floor(log($maxBytes, 1024));
        $est = round($maxBytes / 1024 ** $exp, 1);

        return $est . $units[$exp];
    }

    public function parseSize($size) {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $bytes = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($bytes * 1024 ** mb_stripos('bkmgtpezy', $unit[0]));
        }

        return round($bytes);
    }
}

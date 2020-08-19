<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\EventListener;

use App\Entity\Audio;
use App\Services\FileUploader;
use Doctrine\ORM\Event\LifecycleEventArgs;
use FFMpeg\FFMpeg;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Description of ClippingListener.
 *
 * @author Michael Joyce <ubermichael@gmail.com>
 */
class AudioListener {
    /**
     * @var FileUploader
     */
    private $uploader;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(FileUploader $uploader, LoggerInterface $logger) {
        $this->uploader = $uploader;
        $this->logger = $logger;
    }

    private function uploadFile(Audio $audio) : void {
        $file = $audio->getAudioFile();
        if ( ! $file instanceof UploadedFile) {
            return;
        }
        $audio->setOriginalName($file->getClientOriginalName());

        $filename = $this->uploader->upload($file);
        $path = $this->uploader->getUploadDir() . '/' . $filename;

        $audioFile = new File($path);
        $audio->setFileSize($audioFile->getSize());
        $audio->setAudioFile($audioFile);
        $audio->setAudioPath($filename);
        $audio->setMimeType($audioFile->getMimeType());
    }

    public function prePersist(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($entity instanceof Audio) {
            $this->uploadFile($entity);
        }
    }

    public function preUpdate(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($entity instanceof Audio) {
            $this->uploadFile($entity);
        }
    }

    public function postLoad(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($entity instanceof Audio) {
            $filePath = $this->uploader->getUploadDir() . '/' . $entity->getAudioPath();
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
}

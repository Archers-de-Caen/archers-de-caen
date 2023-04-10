<?php

declare(strict_types=1);

namespace App\Command\V2ToV3;

use App\Domain\File\Model\Document;
use App\Domain\File\Model\Photo;
use App\Domain\File\Model\UploadableInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait DownloadTrait
{
    /**
     * @throws \RuntimeException
     */
    public function downloadFile(string $src): UploadableInterface
    {
        $name = explode('/', $src)[count(explode('/', $src)) - 1];

        if (!$filePath = tempnam(sys_get_temp_dir(), 'download')) {
            throw new \RuntimeException('tempnam bug');
        }

        if (!$file = fopen($filePath, 'wb')) {
            throw new \RuntimeException('fopen bug');
        }

        fwrite($file, @file_get_contents($src) ?: ''); /* @ for ignore warning like http 404 error */
        fclose($file);

        $uploadedFile = new UploadedFile($filePath, $name, test: true);

        $imageExtensions = ['png', 'gif', 'jpg', 'jpeg', 'tif', 'svg', 'webp', 'tiff', 'ico', 'bmp'];

        if (in_array($uploadedFile->getClientOriginalExtension(), $imageExtensions)) {
            $uploadedEntity = (new Photo())->setImageFile($uploadedFile);
        } else {
            $uploadedEntity = (new Document())->setDocumentFile($uploadedFile);
        }

        return $uploadedEntity;
    }
}

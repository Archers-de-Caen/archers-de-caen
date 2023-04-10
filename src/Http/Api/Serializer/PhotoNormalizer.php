<?php

declare(strict_types=1);

namespace App\Http\Api\Serializer;

use App\Domain\File\Model\Photo;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class PhotoNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private readonly UploaderHelper $uploaderHelper,
        private readonly string $baseHost,
    ) {
    }

    /**
     * @param Photo $object
     *
     * @throws ExceptionInterface
     */
    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        /** @var array $data */
        $data = $this->normalizer->normalize($object, $format, $context);

        $url = $this->baseHost.$this->uploaderHelper->asset($object, 'imageFile');

        $data['url'] = $url;

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Photo;
    }
}

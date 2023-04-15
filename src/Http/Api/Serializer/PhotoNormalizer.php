<?php

declare(strict_types=1);

namespace App\Http\Api\Serializer;

use App\Domain\File\Model\Photo;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class PhotoNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly UploaderHelper $uploaderHelper,
        private readonly string $baseHost,
        #[Autowire(service: ObjectNormalizer::class)]
        private readonly NormalizerInterface $normalizer
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

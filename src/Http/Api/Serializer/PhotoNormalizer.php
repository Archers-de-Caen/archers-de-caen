<?php

declare(strict_types=1);

namespace App\Http\Api\Serializer;

use App\Domain\File\Model\Photo;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class PhotoNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private NormalizerInterface $baseNormalizer;

    public function __construct(
        private readonly UploaderHelper $uploaderHelper,
        private readonly CacheManager $cacheManager,
        private readonly string $baseHost,
    ) {
    }

    /**
     * @param Photo $object
     *
     * @throws ExceptionInterface
     */
    #[\Override]
    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        /** @var array $data */
        $data = $this->baseNormalizer->normalize($object, $format, $context);

        $url = $this->baseHost.$this->uploaderHelper->asset($object, 'imageFile');

        $imageName = $object->getImageName();

        $data['url'] = $url;
        $data['urlThumbnail'] = $imageName ? $this->cacheManager->getBrowserPath($imageName, 'thumbnail') : null;
        $data['urlThumbnailMedium'] = $imageName ? $this->cacheManager->getBrowserPath($imageName, 'thumbnail_medium') : null;

        return $data;
    }

    #[\Override]
    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Photo;
    }

    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [
            Photo::class => true,
        ];
    }

    public function setBaseNormalizer(NormalizerInterface $baseNormalizer): void
    {
        $this->baseNormalizer = $baseNormalizer;
    }
}

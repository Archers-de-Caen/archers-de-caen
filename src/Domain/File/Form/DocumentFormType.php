<?php

declare(strict_types=1);

namespace App\Domain\File\Form;

use App\Domain\File\Model\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class DocumentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('documentFile', VichFileType::class, [
                'required' => true,
                'label' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Document $document */
            $document = $event->getData();

            dump('sep 1');
            dump(                !$document->getDisplayText() ,
                !$document->getDocumentFile() ,
                !$document->getDocumentMimeType() ,
                !$document->getDocumentOriginalName() ,
                !$document->getDocumentSize(),
                !$document->getDocumentName());
            dump('sep 2');

            if (
                !$document->getDisplayText() &&
                !$document->getDocumentFile() &&
                !$document->getDocumentMimeType() &&
                !$document->getDocumentOriginalName() &&
                !$document->getDocumentSize() &&
                !$document->getDocumentName()
            ) {
                $event->setData(null);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }
}

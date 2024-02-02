<?php

declare(strict_types=1);

namespace App\Helper;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolation;

class FormHelper
{
    public static function getErrorsArray(FormInterface $form): array
    {
        $errors = [];
        /** @var FormError $error */
        foreach ($form->getErrors() as $error) {
            /** @var ConstraintViolation $cause */
            $cause = $error->getCause();

            $errors[] = [
                'message' => $error->getMessage(),
                'field' => $cause->getPropertyPath(),
                'value' => array_values($cause->getParameters())[0],
            ];
        }

        foreach ($form->all() as $childForm) {
            if (!$childForm instanceof FormInterface) {
                continue;
            }

            if (!($childErrors = self::getErrorsArray($childForm))) {
                continue;
            }

            $errors[$childForm->getName()] = $childErrors;
        }

        return $errors;
    }
}

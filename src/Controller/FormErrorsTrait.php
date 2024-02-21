<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\Form\FormInterface;

trait FormErrorsTrait {
    private function getErrorsFromForm(FormInterface $form) {
        $errors = [];
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }
}

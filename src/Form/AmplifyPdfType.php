<?php

declare(strict_types=1);

namespace App\Form;

use Nines\MediaBundle\Form\PdfType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Contribution form.
 */
class AmplifyPdfType extends PdfType {
    public function buildForm(FormBuilderInterface $builder, array $options, $label = null) : void {
        parent::buildForm($builder, $options, $label);
        $builder->add('description', HiddenType::class, []);
        $builder->add('license', HiddenType::class, []);
    }
}

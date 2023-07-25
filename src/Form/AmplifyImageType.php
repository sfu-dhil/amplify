<?php

declare(strict_types=1);

namespace App\Form;

use Nines\MediaBundle\Form\ImageType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Contribution form.
 */
class AmplifyImageType extends ImageType {
    public function buildForm(FormBuilderInterface $builder, array $options, $label = 'Image File') : void {
        parent::buildForm($builder, $options, $label);
        $builder->add('description', TextareaType::class, [
            'label' => 'Description',
            'required' => true,
            'attr' => [
                'class' => 'tinymce',
            ],
        ]);
        $builder->add('license', HiddenType::class, []);
    }
}

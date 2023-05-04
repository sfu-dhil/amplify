<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Export;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Podcast export form.
 */
class ExportType extends AbstractType {
    /**
     * Add form fields to $builder.
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        $builder->add('format', ChoiceType::class, [
            'label' => 'Export Format',
            'expanded' => false,
            'multiple' => false,
            'choices' => [
                'Islandora' => 'islandora',
                'MODS' => 'mods',
                'Bepress' => 'bepress',
            ],
            'help' => '
                <p>
                    Islandora exports must used with <a href="https://github.com/mjordan/islandora_workbench" target="_blank">Islandora Workbench</a>.
                </p>
            ',
            'help_html' => true,
            'required' => true,
        ]);
    }

    /**
     * Define options for the form.
     *
     * Set default, optional, and required options passed to the
     * buildForm() method via the $options parameter.
     */
    public function configureOptions(OptionsResolver $resolver) : void {
        $resolver->setDefaults([
            'data_class' => Export::class,
        ]);
    }
}

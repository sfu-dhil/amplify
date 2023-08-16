<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Share;
use Nines\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

/**
 * Share form.
 */
class ShareType extends AbstractType {
    /**
     * Add form fields to $builder.
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        $builder->add('user', Select2EntityType::class, [
            'label' => false,
            'required' => true,
            'class' => User::class,
            'remote_route' => 'share_user_typeahead',
            'remote_params' => ['podcast_id' => $builder->getData()->getPodcast()->getId()],
            'allow_clear' => true,
            'placeholder' => 'Search for an existing user by name or email',
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
            'data_class' => Share::class,
        ]);
    }
}

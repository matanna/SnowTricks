<?php

namespace App\Form;

use App\Entity\Tricks;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class TricksType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => false
            ])
            ->add('description', TextareaType::class, [
                'required' => false
            ])
            ->add('photos', CollectionType::class, [
                'entry_type' => FileType::class,
                'label' => false,
                'entry_options' => [
                    'label' => false,
                    'constraints' => [
                        new Image([
                            'maxSize' => '2M',
                            'maxSizeMessage' => 'Votre image ne doit pas dépasser 2Mo',
                            'mimeTypesMessage' => 'Le format de votre image est invalide'
                        ])
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'mapped' => false,
                'prototype' => true,
            ])
            ->add('videos', CollectionType::class, [
                'entry_type' => TextType::class,
                'entry_options' => [
                    'label' => false,
                    'constraints' => [
                        new Regex([
                            'pattern' => '#^<iframe.+></iframe>$#'
                        ])
                    ]
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'label' => false,
                'mapped' => false,
                'prototype' => true,
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tricks::class,
        ]);
    }
}

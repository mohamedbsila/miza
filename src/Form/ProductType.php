<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Subcategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [new NotBlank()],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('price', MoneyType::class, [
                'currency' => 'USD',
                'constraints' => [new NotBlank()],
            ])
            ->add('sku', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'help' => 'SKU must be unique',
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => 'Choose a category',
                'constraints' => [new NotBlank()],
            ])
            ->add('subcategory', EntityType::class, [
                'class' => Subcategory::class,
                'choice_label' => 'name',
                'placeholder' => 'Choose a subcategory',
                'required' => false,
            ])
            ->add('stockQuantity', NumberType::class, [
                'constraints' => [new NotBlank()],
            ])
            ->add('isFeatured', CheckboxType::class, [
                'required' => false,
            ])
            ->add('imageOnFiles', FileType::class, [
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'label' => 'Product Images (ON state)',
            ])
            ->add('imageOffFiles', FileType::class, [
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'label' => 'Product Images (OFF state)',
            ])
            ->add('colors', CollectionType::class, [
                'entry_type' => ProductColorType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'required' => false,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'constraints' => [
                new UniqueEntity([
                    'fields' => 'sku',
                    'message' => 'This SKU is already in use.',
                ]),
            ],
        ]);
    }
} 
<?php

namespace App\Form;

use App\Entity\ProductColor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductColorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('colorName', TextType::class, [
                'constraints' => [new NotBlank()],
                'label' => 'Color Name',
                'attr' => ['placeholder' => 'e.g. Antique Brass'],
            ])
            ->add('colorCode', ColorType::class, [
                'constraints' => [new NotBlank()],
                'label' => 'Color Code',
                'html5' => true,
            ])
            ->add('imageOnFile', FileType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'ON State Image',
            ])
            ->add('imageOffFile', FileType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'OFF State Image',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductColor::class,
        ]);
    }
} 
 
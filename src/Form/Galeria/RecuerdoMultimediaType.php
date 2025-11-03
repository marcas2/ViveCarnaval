<?php

namespace App\Form\Galeria;

use App\Entity\Galeria\RecuerdoMultimedia;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class RecuerdoMultimediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titulo', TextType::class, [
                'label' => 'Título',
                'attr' => ['placeholder' => '¿Qué estás recordando?', 'class' => 'form-control']
            ])
            ->add('descripcion', TextareaType::class, [
                'label' => 'Descripción',
                'attr' => ['placeholder' => 'Cuéntanos tu recuerdo...', 'rows' => 4, 'class' => 'form-control']
            ])

            ->add('multimedia', FileType::class, [
        'label' => 'Archivo multimedia (imagen o video)',
        'mapped' => false, // no se guarda directamente en la entidad
        'required' => false,
        'constraints' => [
            new File([
                'maxSize' => '200M',
                'mimeTypes' => [
    'image/jpeg', 'image/png', 'image/gif',
    'video/mp4', 'video/avi', 'video/mpeg',
    'video/quicktime', 'video/x-msvideo', 'video/x-matroska'
],

                'mimeTypesMessage' => 'Solo se permiten imágenes o videos válidos (jpg, png, gif, mp4, avi, mpeg, mov).',
            ])
        ],
        'attr' => [
            'class' => 'form-control',
            'accept' => 'image/*,video/*' // 👈 limita la selección en el explorador de archivos
        ]
    ])
;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RecuerdoMultimedia::class,
        ]);
    }
}

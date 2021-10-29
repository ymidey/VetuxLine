<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class MergeCsvForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                "label" => "Type de fusion",
                'choices'  => [
                    'Séquentiel' => "Séquentiel",
                    'Entrelacé' => "Entrelacé"
                ]
            ])
            ->add('csv1', FileType::class, [
                "label" => "CSV 1",
                "constraints" => [
                    new NotBlank(),
                    new File([
                        'mimeTypes' => [
                            'text/x-csv',
                            'text/csv',
                            'application/x-csv',
                            'application/csv',],
                        "mimeTypesMessage" => "Seul les fichiers Csv sont autorisées !"
                    ])
                ]
            ])
            ->add('csv2', FileType::class, [
                "label" => "CSV 2",
                "constraints" => [
                    new NotBlank(),
                    new File([
                        'mimeTypes' => [
                            'text/x-csv',
                            'text/csv',
                            'application/x-csv',
                            'application/csv',],
                        "mimeTypesMessage" => "Seul les fichiers Csv sont autorisées !"
                    ])
                ]
            ])
            ->setMethod("POST")
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }

}

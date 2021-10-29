<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class EtlCsvForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('csv', FileType::class, [
                "label" => "CSV",
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

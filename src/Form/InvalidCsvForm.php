<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class InvalidCsvForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('type', ChoiceType::class, [
                "label" => "Type de tri",
                'choices' => [
                    'Tout les clients invalides' => "allClient",
                    'Client non majeur' => "notMajor",
                    'Client avec un taille en inch et une taille en cm qui ne correspondent pas ' => "invalidSize",
                    'Client avec un code de carte de crédit en doublon avec un autre client' => "invalidCcNumber",
                ]
            ])
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
            ->setMethod("POST");
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}

<?php
namespace App\Form;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CsvFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('csv_file', FileType::class, [
            'label' => 'Select CSV file',
            'required' => true,
            'mapped' => false,
            'constraints' => [
                new \Symfony\Component\Validator\Constraints\File([
                    'maxSize' => '8196k',
                    'mimeTypes' => [
                        'text/csv',
                        'text/json',
                        'application/vnd.ms-excel',
                        'text/plain',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid CSV document',
                ])
            ],
        ]);
    }
}
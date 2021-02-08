<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code')
            ->add('name')
            ->add('description')
            ->add('brand')
            ->add('price')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('category', EntityType::class, [
                'class'    => Category::class,
                'mapped' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}

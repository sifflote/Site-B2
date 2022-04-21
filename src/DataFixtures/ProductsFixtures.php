<?php

namespace App\DataFixtures;

use App\Entity\Commerce\Products;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProductsFixtures extends Fixture
{
    public function __construct(private SluggerInterface $slugger){}

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        for($prod = 1; $prod <= 50; $prod++){
            $product = new Products();
            $product->setName($faker->text(15));
            $product->setDescription($faker->text());
            $product->setSlug($this->slugger->slug($product->getName())->lower());
            $product->setPrice($faker->numberBetween(900,150000));
            $product->setStock($faker->numberBetween(0,10));

            // On va chercher une référence de catégorie
            $category = $this->getReference('cat-'.rand(1, 8));
            $product->setCategories($category);

            $this->setReference('prod-'.$prod, $product);


            $manager->persist($product);
        }
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}

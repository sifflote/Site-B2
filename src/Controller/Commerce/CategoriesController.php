<?php

namespace App\Controller\Commerce;

use App\Entity\Commerce\Categories;
use App\Entity\Commerce\Products;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/commerce/categories', name: 'commerce_categories_')]
class CategoriesController extends AbstractController
{

    #[Route('/{slug}', name: 'list')]
    public function list(Categories $category): Response
    {
        //On va chercher la liste des produits de la catégorie
        $products = $category->getProducts();

        return $this->render('commerce/categories/list.html.twig', compact('category', 'products'));
        //return $this->render('commerce/categories/list.html.twig', [
        //  'category' => $category,
        // 'products' => $products
        // ]);

    }
}

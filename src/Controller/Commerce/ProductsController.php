<?php

namespace App\Controller\Commerce;

use App\Entity\Commerce\Products;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/commerce/produits', name: 'products_')]
class ProductsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('commerce/products/index.html.twig', [
            'controller_name' => 'ProductsController',
        ]);
    }

    #[Route('/{slug}', name: 'details')]
    public function details(Products $product): Response
    {
        return $this->render('commerce/products/details.html.twig', compact('product'));
    }
}

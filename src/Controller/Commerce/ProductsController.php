<?php

namespace App\Controller\Commerce;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/commerce/products', name: 'products_')]
class ProductsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('commerce/products/index.html.twig', [
            'controller_name' => 'ProductsController',
        ]);
    }

    #[Route('/slug', name: 'details')]
    public function details(): Response
    {
        return $this->render('commerce/products/details.html.twig');
    }
}

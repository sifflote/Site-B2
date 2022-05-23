<?php

namespace App\Controller\Commerce;

use App\Repository\Commerce\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EcommerceController extends AbstractController
{
    #[Route('/commerce/', name: 'commerce_index')]
    public function index(CategoriesRepository $categoriesRepository): Response
    {
        return $this->render('commerce/index.html.twig', [
            'categories' => $categoriesRepository->findAll([],
                ['categoryOrder' => 'asc'])
        ]);
    }
}

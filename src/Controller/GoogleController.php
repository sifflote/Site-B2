<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GoogleController extends AbstractController
{
    #[Route('oauth/google', name:'connect_google')]
    public function connectAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry
            ->getClient('google')
            ->redirect();
    }

    #[Route('oauth/check/google', name:'connect_google_check')]
    public function connectCheckAction(Request $request)
    {
        if(!$this->getUser()) {
            return new JsonResponse(array('status' => false, 'message' => "Utilisateur non trouvé!"));
        } else {
            return $this->redirectToRoute('profile_index');
        }
    }

}
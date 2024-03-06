<?php

namespace App\Controller;
use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Commande;


class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(): Response
    {
        return $this->render('cart/index.html.twig', [
            'controller_name' => 'CartController',
        ]);
    }
    #[Route('/carddddd', name: 'app_commande_indexcart', methods: ['GET'])]
    public function display_all_in_card(CommandeRepository $commandeRepository): Response
    {
        return $this->render('commande/cart.html.twig', [
            'commandes' => $commandeRepository->findAll(),
        ]);
    }

}

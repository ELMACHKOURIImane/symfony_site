<?php

namespace App\Controller;

use App\Repository\PanierRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product ;
use App\Entity\User ;
use Doctrine\Common\Collections\ArrayCollection;

class PanierController extends AbstractController
{

    private $panierrepository ; 
    private $entityManager ; 

    public function __construct(PanierRepository $panierrepo , ManagerRegistry $entitymanager)
    {
        $this->panierrepository = $panierrepo ;
        $this->entityManager = $entitymanager->getManager();
        
    }
    #[Route('/panier', name: 'app_panier')]
    public function index(): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('login');    
            }
            return $this->render('panier/index.html.twig', [
                'user' => $this->getUser(),
            ]);
    }
    #[Route('/panier/add/{product}/{user}', name: 'add_panier')]
    public function AddToPanel(Product $product , User $user): Response
    {
        $panier = $user->getPanier();
        $panier->addProduct($product);
        $panier->setPrice($panier->getPrice()+$product->getPrice());
        $product->setPanier($panier);
        $this->entityManager->persist($panier);
        $this->entityManager->persist($product);
        $this->entityManager->flush();
    
    return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/remove/{product}/{user}', name: 'remove_from_panier')]
    public function RemoveFromPanel(Product $product , User $user): Response
    {
        $panier = $user->getPanier();
        $panier->removeProduct($product);
        $this->entityManager->persist($panier);
        $this->entityManager->flush();
    
    return $this->redirectToRoute('app_panier');
    }
    

}

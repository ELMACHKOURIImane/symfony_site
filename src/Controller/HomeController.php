<?php

namespace App\Controller;

use App\Entity\Cathegorie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CathegorieRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{
 
    private $productRepository ;
    private $entityManager ;
    private $cathegorieRepository ;

    public function __construct(ProductRepository $productrepos , ManagerRegistry $doctrine , CathegorieRepository $cathegoriRepo)
    {
     $this->productRepository = $productrepos ;  
     $this->entityManager = $doctrine->getManager() ; 
     $this->cathegorieRepository = $cathegoriRepo; 
         
    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        $products = $this->productRepository->findAll();
        $cathegories = $this->cathegorieRepository->findAll();
        return $this->render('home/index.html.twig', [
            'products' => $products,
            'cathegories' => $cathegories 
        ]);
    }
#[Route('/show/cathegorie/{cathegorie}', name: 'cathegories')]
public function CathegoriesProduct(Cathegorie $cathegorie): Response
{
    $cathegories = $this->cathegorieRepository->findAll();
    return $this->render('home/index.html.twig', [
        'products' => $cathegorie->getProducts(),
        'cathegories' => $cathegories 
    ]);
}



}

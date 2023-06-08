<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Panier;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Product;
use Symfony\Component\Security\Http\Attribute\IsGranted;
class OrderController extends AbstractController
{

    private $orderrepository ;
    private $entityManager ;

    public function __construct(OrderRepository $ordertrepos , ManagerRegistry $doctrine)
    {
     $this->orderrepository = $ordertrepos ;  
     $this->entityManager = $doctrine->getManager() ;
         
    }

    #[Route('/orders', name: 'orders_list')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        $orders = $this->orderrepository->findAll();
        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }
    #[Route('/user/orders', name: 'user_orders')]
    public function UserOrders(): Response
    {
        if(!$this->getUser()){
        return $this->redirectToRoute('login');    
        }
        return $this->render('order/user.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/register/order/{panier}', name: 'register_order')]
    public function AddProduct(Panier $panier): Response
    {
        if(!$this->getUser()){
        return $this->redirectToRoute('login');    
        }
        $order = new Order();
        $order->setPname($panier->getPanierName());
        $order->setPrice($panier->getPrice());
        $order->setStatus('Processing...');
        $order->setUser($this->getUser());
            $this->entityManager->persist($order);
            $this->entityManager->flush();
        return $this->redirectToRoute('user_orders');
    }
    #[Route('/order/update/{order}/{status}', name: 'update_status')]
    public function UpdateStatusOrder(Order $order , $status): Response
    {
        $order->setStatus($status);
        $this->entityManager->persist($order);
            $this->entityManager->flush();
        return $this->redirectToRoute('orders_list');
    }
    #[Route('/order/delete/{order}', name: 'delete_Order')]
    public function DeleteOrder(Order $order): Response
    {
       
        $this->entityManager->remove($order);
            $this->entityManager->flush();
        return $this->redirectToRoute('orders_list');
    }

}

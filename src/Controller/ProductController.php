<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProductController extends AbstractController
{

    private $productRepository ;
    private $entityManager ;

    public function __construct(ProductRepository $productrepos , ManagerRegistry $doctrine)
    {
     $this->productRepository = $productrepos ;  
     $this->entityManager = $doctrine->getManager() ;
         
    }
    #[Route('/product', name: 'product_list')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        $products = $this->productRepository->findAll();
        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }
    #[Route('/register/product', name: 'product_register')]
    #[IsGranted('ROLE_ADMIN')]
    public function AddProduct(Request $request , SluggerInterface $slugger): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class ,$product);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $product = $form->getData() ; 
            $brochureFile = $form->get('image')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('image_directory'),
                        $newFilename
                    );
                    
                }catch (FileException $e) {
                        // ... handle exception if something happens during file upload
            }
            $product->setImage($newFilename);

            $this->entityManager->persist($product);
            $this->entityManager->flush();
            $this->addFlash(
                'succes',
                'Your product was saved'
            );
        return $this->redirectToRoute('product_list');
        }
    }
    return $this->renderForm('product/create.html.twig', [
        'form' => $form,
    ]);
}
#[Route('/show/product/{id}', name: 'product')]
public function Show(Product $product): Response
{
    return $this->render('product/show.html.twig', [
        'product' => $product,
    ]);
}
#[Route('/edit/product/{id}', name: 'edit')]
#[IsGranted('ROLE_ADMIN')]
public function Edit(Product $product , Request $request , SluggerInterface $slugger): Response
{
    
    $form = $this->createForm(ProductType::class ,$product);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $product = $form->getData() ; 
            $brochureFile = $form->get('image')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('image_directory'),
                        $newFilename
                    );
                    
                }catch (FileException $e) {
                        // ... handle exception if something happens during file upload
            }
            $product->setImage($newFilename);

        }
        $this->entityManager->persist($product);
        $this->entityManager->flush();
        $this->addFlash(
            'succes',
            'Your product was updated'
        );
        return $this->redirectToRoute('product_list');
    }
    return $this->renderForm('product/edit.html.twig', [
        'form' => $form,
    ]);
}

#[Route('/delete/product/{id}', name: 'delte')]
#[IsGranted('ROLE_ADMIN')]
public function Delete(Product $product , Request $request , $id): Response
{
   $fileSystem = new Filesystem();
   $imagePath = './uploads/'.$product->getImage();
   if($fileSystem->exists($imagePath)){
         $fileSystem->remove($imagePath);
    }
    $this->entityManager->remove($product);
    $this->entityManager->flush();
    return $this->redirectToRoute('product_list');
}


}

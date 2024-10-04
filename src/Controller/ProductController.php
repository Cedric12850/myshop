<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Response as BrowserKitResponse;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();
        return $this->render('product/index.html.twig', [
            'products' => $products
        ]);
    }

    #[Route('/product/add', name: 'app_product_add')]
    public function add(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/upload/')] string $uploadDirectory

        ): Response
    {
        $newProduct = new Product;
        $form = $this->createForm(ProductType::class, $newProduct);
        $form->handleRequest($request);

        if($form->isSubmitted()&& $form->isValid()){
             /** @var UploadedFile $brochureFile */
             $thumbnail = $form->get('thumbnail')->getData();

             if($thumbnail) {
                //recupère le nom d'origine de l'image
                $originalFilename = pathinfo($thumbnail->getClientOriginalName(), PATHINFO_FILENAME); 
                //$slugger nettoie le nom de l'image en enlever les espaces ou autres, ...
                $safeFilename = $slugger->slug($originalFilename);
                //une fois le nom nettoyer, on lui attribue un id unique et guessExtension rajoute le .jpg ou . png ou autre
                $newFilename = $safeFilename.'-'.uniqid().'.'.$thumbnail->guessExtension();


                // Move the file to the directory where brochures are stored
                try {
                    $thumbnail->move($uploadDirectory, $newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'uploadFilename' property to store the PDF file name
                // instead of its contents
                $newProduct->setThumbnail($newFilename);
            }

            $newProduct = $form->getData();
            $entityManager->persist($newProduct);
            $entityManager->flush();

            return $this->redirectToRoute('app_product');
        }
        return $this->render('product/add.html.twig', [
            'formulaire'=>$form
        ]);
    }

    //Afficher un article par son id
    #[Route('/product/{id}', name: 'app_product_id')]
    public function showById(ProductRepository $productRepository, int $id):Response
    {
        $product = $productRepository->find($id);
        dump($product);
        return $this->render('product/id.html.twig', [
            'product' => $product
        ]);
    }


    #[Route('/product/edit/{id}', name: 'app_product_edit')]
    public function update(
        EntityManagerInterface $entityManager,
        int $id
        ):Response
        {
            $product = $entityManager->getRepository(Product::class)->find($id);
            if(!$product){
                throw $this->createNotFoundException(
                    "Le produit n'a pas été trouvé".$id
                );
            }
            $product->setName();
            $entityManager->flush();
            return $this->redirectToRoute('app_product', [
                'id'=>$product->getId()
            ]);
        }

}

<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\DependencyInjection\DependencyInjectionExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function index(): Response
    {
        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
        ]);
    }

    #[Route('/category/add', name: 'app_category_add')]
    public function add(Request $request, EntityManagerInterface $entityManager ): Response
    {
        //Création d'un objet vide de type catégory
        $newcategory = new Category;
        //initialisation d'un formulaire à partir de la classe de formulaire correspondant à l'entité, puis on le relie à l'objet vide.
        $form = $this->createForm(CategoryType::class, $newcategory );
        //on demande au formulaire de traiter les requètes. On lui fournit donc un objet request injecté dans la fonction add()
        $form->handleRequest($request);
        //On lui indique quoi faire des données: surveiller si submit et valide
        if($form->isSubmitted()&& $form->isValid()){
            //alors on va remplir l'objet vide avec les données du formulaire
            $newcategory = $form->getData();
            // le persist va sauvegarder l'entité puis le flush va l'envoyer en bdd
            $entityManager->persist($newcategory);
            $entityManager->flush();

            return $this->redirectToRoute('app_product');

        }
        
        return $this->render('category/add.html.twig', [
            //on envoie le formulaire à la vue
            'formulaire'=>$form
        ]);
    }

    #[Route('/category/{id}', name: 'app_category_show')]
    public function show(CategoryRepository $categoryRepository, $id):Response
    {
        $category = $categoryRepository->find($id);
        dump($category);
        return $this->render('category/show.html.twig', [
            'category' =>$category
        ]);
    }
}

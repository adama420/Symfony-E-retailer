<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\Uploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/", name="product_index", methods={"GET"})
     * @param ProductRepository $productRepository
     * @param CategoryRepository $categoryRepository
     * @return Response
     */
    public function index(ProductRepository $productRepository, CategoryRepository $categoryRepository)
    {
        $colors=['red','green','blue','orange','yellow'];

        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
            'categories' => $categoryRepository->findAll(),
            'colors' => $colors,

        ]);

    }

    /**
     * @Route("/new", name="product_new", methods={"GET","POST"})
     * @param Request $request
     * @param Uploader $uploader
     * @return Response
     */
    public function new(Request $request, Uploader $uploader)
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            if (!$product->getUser()) {
                $product->setUser($this->getUser());
            }

            /** @var UploadedFile $image */
            if($image = $form->get('image')->getData()){
                $fileName = $uploader->upload($image);
                $product->setImage($fileName);

            }


            //on veut ajouter l'objet en BDD
            $entityManager = $this->getDoctrine()->getManager();
            // alors on récupere l'entity manage avec Doctrine
            $entityManager->persist($product);
            // on commit l'objet (comme GIt)
            $entityManager->flush();
            //on push l'objet(comme GIt)
            $this->addFlash('success', 'Le produit a bien été ajouté.');


        }
        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="product_show", methods={"GET"})
     * @param Product $product
     * @return Response
     */
    public function show(Product $product)
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,

        ]);
    }

    /**
     * @Route("/{id}/edit", name="product_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Product $product
     * @param Uploader $uploader
     * @return RedirectResponse|Response
     */
    public function edit(Request $request, Product $product, Uploader $uploader)
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $image */
            if($image = $form->get('image')->getData()){
                if ($product->getImage()){
                    $uploader->remove($product->getImage());
                }

                $fileName= $uploader->upload($image);
                $product->setImage($fileName);

            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="product_delete", methods={"DELETE"})
     * @param Request $request
     * @param Product $product
     * @param Uploader $uploader
     * @return RedirectResponse
     */
    public function delete(Request $request, Product $product, Uploader $uploader)
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            if($product->getImage()){
                $uploader->remove($product->getImage());
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('product_index');
    }
}

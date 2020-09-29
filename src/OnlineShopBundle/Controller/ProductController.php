<?php

namespace OnlineShopBundle\Controller;

use OnlineShopBundle\Entity\Category;
use OnlineShopBundle\Entity\Likes;
use OnlineShopBundle\Entity\Product;
use OnlineShopBundle\Entity\User;
use OnlineShopBundle\Form\ProductType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends Controller
{
    /**
     * @Route("/create", name="product_create")
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request)
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        $category = $this
            ->getDoctrine()
            ->getRepository(Category::class)
            ->findAll();

        if($form->isSubmitted())
        {
            $this->uploadFile($form, $product);

            $product->setAuthor($this->getUser());
           $em = $this->getDoctrine()->getManager();
           $em->persist($product);
           $em->flush();

           return $this->redirectToRoute("shop_index");
        }

        return $this->render('products/create.html.twig',
            ['form' => $form->createView(),
                'category' => $category]);
    }

    /**
     * @Route("/edit/{id}", name="product_edit")
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, $id)
    {
        $product = $this
            ->getDoctrine()
            ->getRepository(Product::class)
            ->find($id);

        if(null === $product) {
            return $this->redirectToRoute("shop_index");
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        
        if(!$currentUser->isAdmin()){
        return $this->redirectToRoute("shop_index");
    }


        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if($form->isSubmitted())
        {
            $this->uploadFile($form, $product);
            $em = $this->getDoctrine()->getManager();
            $em->merge($product);
            $em->flush();
            return $this->redirectToRoute("shop_index");
        }

        return $this->render('products/edit.html.twig',
            [
                'form' => $form -> createView(),
                'product' => $product
            ]);
    }


    /**
     * @Route("/delete/{id}", name="product_delete")
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request, int $id)
    {
        $product = $this
            ->getDoctrine()
            ->getRepository(Product::class)
            ->find($id);

        if(null === $product) {
            return $this->redirectToRoute("shop_index");
        }

        if(!$this->isAdmin($product)){
            return $this->redirectToRoute("shop_index");
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->remove('image');
        $form->handleRequest($request);
        if($form->isSubmitted())
        {
            $em = $this->getDoctrine()->getManager();
            $em->remove($product);
            $em->flush();

            return $this->redirectToRoute("shop_index");
        }

        return $this->render('products/delete.html.twig',
            [
                'form' => $form->createView(),
                'product' => $product
            ]);
    }


    /**
     * @Route("/product/{id}", name="product_view")
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function view($id)
    {   
        $product = $this
            ->getDoctrine()
            ->getRepository(Product::class)
            ->find($id);

    if(null === $product){
        return $this->redirectToRoute("shop_index");
    }

        return $this->render("products/view.html.twig",
            ['product' => $product]);
    }

    /**
     * @param Product $product
     * @return bool
     */
    private function isAdmin(Product $product)
    {
        /** @var  User $currentUser */
        $currentUser = $this->getUser();

        if(!$currentUser->isAdmin()){
                return false;
        }
        return true;
    }



    /**
     * @param \Symfony\Component\Form\FormInterface $form
     * @param Product $product
     */
    public function uploadFile(FormInterface $form, Product $product)
    {
        /** @var UploadedFile $file */
        $file = $form['image']->getData();

        $fileName = md5(uniqid()) . '.' . $file->guessExtension();

        if ($file) {
            $file->move(
                $this->getParameter('product_directory'),
                $fileName
            );

            $product->setImage($fileName);
        }
    }

    /**
     * @Route("/add/{id}", name="like")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function Like(Request $request, $id)
    {
        echo "I'm Here!";
        $user = $this->getUser();

        $product = $this
            ->getDoctrine()
            ->getRepository(Product::class)
            ->find($id);
        $value1 = $user->getId();
        $value2 = $product->getId();
        $count = 0;
        $likes = $this->getDoctrine()->getRepository(Likes::class)->findAll();
//        var_dump($likes);

        foreach ($likes as $k => $like) {
            if ($value1 == $like->getUserId()) {
                $count++;
            }
        }



        foreach ($likes as $k=>$like) {
//          var_dump($like->getUserId());
//          var_dump($like->getProductId());
          if ($value1==$like->getUserId() && $value2==$like->getProductId())
          {
//              echo "Съвпада\n";
//              return $this->render("proba/proba.html.twig",
//                  ['count' => $count, 'likes'=>7]);
              return $this->redirectToRoute("shop_index");

          }

        }


$likes = new Likes();

$likes->setUserId($value1);
$likes->setProductId($value2);
$em = $this->getDoctrine()->getManager();
$em->persist($likes);
$em->flush();
       // echo $count;

        return $this->redirectToRoute("shop_index");

//        return $this->render("proba/proba.html.twig",
//            ['count' => $count + 1]);

    }




    /**
     * @Route("/add/{id}", name="add_to_cart")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function AddToCart(Request $request, $id)
    {
        $user = $this->getUser();

        $product = $this
            ->getDoctrine()
            ->getRepository(Product::class)
            ->find($id);

        return $this->redirectToRoute("shop_index");
    }



}

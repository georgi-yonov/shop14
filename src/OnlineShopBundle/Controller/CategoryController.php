<?php

namespace OnlineShopBundle\Controller;

use OnlineShopBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends Controller
{
    /**
     * @Route("/category/{id}", name="products_edit")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function findAllByCategory($id)
    {
        $products = $this
            ->getDoctrine()
            ->getRepository(Product::class)
            ->findBy(
                ['category' => $id]
            );

        if(empty($products)){
            return $this->render('category/empty.html.twig');
        }else {
            return $this->render('category/products.html.twig',
                ['products' => $products]);
        }

    }
}

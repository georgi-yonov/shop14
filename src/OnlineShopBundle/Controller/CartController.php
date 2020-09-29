<?php

namespace OnlineShopBundle\Controller;

use OnlineShopBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use OnlineShopBundle\Service\LikesService;

class CartController extends Controller
{
    /**
     * @Route("/cart", name="basket")
     */
    public function indexAction(Request $request, LikesService $likesService)
    {
        $session = $request->getSession();
        $cartElements = $session->get('cartElements');
        if(empty($cartElements)){
            return $this->render('cart/empthy.html.twig');
        }else {
            $cartElementsDetails = array();
            foreach ($cartElements as $id=>$amout) // в id-то пазим id-то на продукта, а в amout-то пазим количеството
            {
                $cartElementDetail = array(); // всеки път като се въртим в цикъла правим празен масив
                $product = $this // взимаме продукта от базата данни
                    ->getDoctrine()
                    ->getRepository(Product::class)
                    ->find($id);
                $cartElementDetail['product']=$product; // добавяме продукта в празния масив
                $cartElementDetail['amout']=$amout; // добавяме количеството на този продукт в кошницата
                $cartElementsDetails[$id]=$cartElementDetail; // добавяме за всеки продукт в количката пълната информация за продукта + количеството
            };

            $user = $this->getUser();
            $likes = 0;
            if ($user) {
                $likes =  $likesService->getLikes($user);
            }
            
            return $this->render('cart/index.html.twig',
                ['cartElementsDetails' => $cartElementsDetails, 'likes'=>$likes]);
        }
    }

    /**
     * @Route("/cart/add/{id}", name="cart_add")
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request, $id)
    {
        $product = $this
            ->getDoctrine()
            ->getRepository(Product::class)
            ->find($id);

        if(null === $product) {
            return $this->redirectToRoute("shop_index"); // ако не е намерен продукта редиректва към shop_index
        }

        $session = $request->getSession(); // от тук имаме достъп до сесията
        $cartElements = $session->get('cartElements'); // взимаме елементите от Shoping картата
       // $cartElements[$product->getId()]=1;
        // $element = array('');
        $cartElements[$product->getId()]=
            (isset($cartElements[$product->getId()]))? $cartElements[$product->getId()]+1 : 1; // $product->getName(); // масив, от който взимаме на продукта името
//         print_r($cartElements);
//         exit();
        $session->set('cartElements', $cartElements);
        return $this->render('cart/add.html.twig',
                ['product' => $product]);

    }

}

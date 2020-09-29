<?php

namespace OnlineShopBundle\Controller;

use OnlineShopBundle\Entity\Product;
use OnlineShopBundle\Entity\Likes;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OnlineShopBundle\Service\LikesService;

class HomeController extends Controller
{
    /**
     *
     * @Route("/", name="shop_index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, LikesService $likesService)
    {
        $user = $this->getUser();
     //  $testService =  $service->getLikes($user);
        $likes = 0;
        if ($user) {
      //      $likes = $this->getDoctrine()->getRepository(Likes::class)->findBy(array('userId'=>$user->getId()));
            $likes =  $likesService->getLikes($user);
        }

        $products = $this->getDoctrine()->getRepository(Product::class)
            ->findAll();

        return $this->render('home/index.html.twig',
            ['products' => $products, 'likes' => $likes]); // от тук в base.html.twig
    }
    
    
    public function getLikes(LikesService $likes) {
        return $likes->getLikes($this->getUser());
    }
}

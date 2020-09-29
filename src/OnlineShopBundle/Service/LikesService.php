<?php

namespace OnlineShopBundle\Service;

use Doctrine\ORM\EntityManagerInterface;

use OnlineShopBundle\Entity\Likes;

class LikesService {
    
    private $_entityManager;
    
    public function __construct(EntityManagerInterface $entityManager) {
       $this->_entityManager = $entityManager; 
    }
    
    public function getLikes($user) {
        $likes = array('123');
        if ($user) {
            $likes = $this->_entityManager->getRepository(Likes::class)->findBy(array('userId'=>$user->getId()));
        }
        
        return count($likes);
    }
}
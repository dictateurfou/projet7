<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Entity\Product;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Service\JwtManager;
use App\Service\EncoderJson;


/**
 * @Route("/api", name="api_")
 */
class ApiController extends AbstractController
{
    /**
    * @Route("/register", name="register")
    */
    public function register(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $em = $this->getDoctrine()->getManager();
        $username = $request->request->get('_username');
        $password = $request->request->get('_password');
        $user = new User($username);
        $user->setPassword($encoder->encodePassword($user, $password));
        $em->persist($user);
        $em->flush();
        return new Response(sprintf('User %s successfully created', $user->getUsername()));
    }

    /**
    * @Route("/test", name="test")
    */
    public function test(JwtManager $jwtManager)
    {
        
        return new Response($this->getUser()->getUsername());
    }

    /**
    * @Route("/getJwt/{apiKey}", name="getjwt")
    */
    public function getJwt(JwtManager $jwtManager, $apiKey)
    {
        $this->getDoctrine()->getManager();
        $user = $this->getDoctrine()
        ->getRepository(User::class)
        ->findBy(['apiKey' => $apiKey]);
        if($user !== null){
            $data = ["apiKey" => $user[0]->getApiKey()];
            $token = $jwtManager->createJwt($data);
        
            return new Response($token);
        }
        
    }

    /**
    *@Route("/getAllProducts", name="getallproduct")
    */
    public function getAllProducts(EncoderJson $encoderJson)
    {
        $repository = $this->getDoctrine()->getRepository(Product::class);
        $products = $repository->findAll();
        $jsonObject = $encoderJson->encodeData($products);
        return new Response($jsonObject);
    }


    /**
    *@Route("/getProductInfo/{productId}", name="getproductinfo")
    */
    public function getProductInfo(EncoderJson $encoderJson,$productId)
    {
        $repository = $this->getDoctrine()->getRepository(Product::class);
        $product = $repository->find($productId);
        if($product !== null){
            $jsonObject = $encoderJson->encodeData($product);
            return new Response($jsonObject);
        }

        return new Response(json_encode(["error" => "this product doesn't exist"]));
    }


}

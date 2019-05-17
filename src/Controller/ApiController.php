<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Entity\Product;
use App\Entity\Client;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Service\JwtManager;
use App\Service\EncoderJson;
use JMS\Serializer\SerializerInterface;


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
    *
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
    *@Route("/products", name="getallproduct", methods={"GET"})
    */
    public function getAllProducts(SerializerInterface $serializer,EncoderJson $encoderJson)
    {
        $repository = $this->getDoctrine()->getRepository(Product::class);
        $products = $repository->findAll();
        $jsonObject = $serializer->serialize($products, 'json');
        
        return new Response($jsonObject);
    }


    /**
    *@Route("/products/{id}", name="product_get", methods={"GET"})
    *
    */
    public function getProductInfo(EncoderJson $encoderJson,$id)
    {
        $repository = $this->getDoctrine()->getRepository(Product::class);
        $product = $repository->find($id);
        if($product !== null){
            $jsonObject = $encoderJson->encodeData($product);
            return new Response($jsonObject);
        }

        return new Response(json_encode(["error" => "this product doesn't exist"]));
    }


    /**
    *@Route("/clients", name="client_add", methods={"POST"})
    */
    public function addClient(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository(Client::class);
        $data = json_decode($request->getContent(),true);
        if( !array_key_exists("username",$data) ){
            return new Response(json_encode(["error" => "username data is missing"]));
        }

        $client = $repository->findOneBy(["api" => $this->getUser(), "username" => $data["username"]]);
        if(null !== $client){
            return new Response(json_encode(["error" => "this username is already taken"]));
        }

        $client = new Client();
        $client->setUsername($data["username"]);
        $client->setApi($this->getUser());
        $em->persist($client);
        $em->flush();

        return new Response(json_encode(["sucess" => "this user is now register"]));

    }


    /**
    *@Route("/clients/{id}", name="client_delete", methods={"DELETE"})
    */
    public function removeClient(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository(Client::class);
        $client = $repository->findOneBy(["api" => $this->getUser(), "id" => $id]);
        if(null === $client){
            return new Response(json_encode(["error" => "this user doesn't exist"]));
        }
        
        $em->remove($client);
        $em->flush();

        return new Response(json_encode(["sucess" => "this user is now delete"]));

    }


    /**
    *@Route("/clients", name="client", methods={"GET"})
    */
    public function getClients(SerializerInterface $serializer,EncoderJson $encoderJson)
    {
        $repository = $this->getDoctrine()->getRepository(Client::class);
        $clients = $repository->findBy(["api" => $this->getUser()]);
        $jsonObject = $serializer->serialize($clients, 'json');
        return new Response($jsonObject);
    }

    /**
    *@Route("/clients/{id}", name="client_get" , methods={"GET"})
    */
    public function clientInfo(EncoderJson $encoderJson,$id)
    {
        $repository = $this->getDoctrine()->getRepository(Client::class);
        $client = $repository->findOneBy(["id" => $id]);
        if(null === $client){
            return new Response(json_encode(["error" => "user doesn't exist"]));
        }

        return new Response($encoderJson->encodeData($client));
    }

}

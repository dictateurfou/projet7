<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Entity\Client;
use App\Repository\ClientRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Service\JwtManager;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
        $username = $request->request->get('username');
        $password = $request->request->get('password');
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
        if ($user !== null) {
            $data = ["apiKey" => $user[0]->getApiKey()];
            $token = $jwtManager->createJwt($data);
        
            return new Response($token);
        }
    }

    /**
    * @Route("/products", name="getallproduct", methods={"GET"})
    * @SWG\Response(
    *   response=200,
    *   description="get all products",
    *   @SWG\Schema(
    *       type="array",
    *       @SWG\Items(ref=@Model(type=Product::class))
    *   )
    * )
    *
    * @SWG\Tag(name="products")
    * @Security(name="Bearer")
    */
    public function getAllProducts(SerializerInterface $serializer, ProductRepository $repository)
    {
        $products = $repository->findAll();
        $jsonObject = $serializer->serialize($products, 'json');
        return (new JsonResponse($jsonObject, 200, [], true))
        ->setSharedMaxAge(3600);
    }


    /**
    *@Route("/products/{id}", name="product_get", methods={"GET"})
    * @SWG\Response(
    *   response=200,
    *   description="get spécific product",
    *   @SWG\Schema(ref=@Model(type=Product::class))
    * )
    * @SWG\Response(
    *   response=400,
    *   description="product doesn't exist",
    * )
    *
    * @SWG\Tag(name="products")
    * @Security(name="Bearer")
    */
    public function getProductInfo(SerializerInterface $serializer, $id, ProductRepository $repository)
    {
        $product = $repository->find($id);
        if ($product !== null) {
            $jsonObject = $serializer->serialize($product, 'json');
            return (new JsonResponse($jsonObject, 200, [], true))
            ->setSharedMaxAge(3600);
        }

        return new JsonResponse(["error" => "this product doesn't exist"], 400);
    }


    /**
    *@Route("/clients", name="client_add", methods={"POST"})
    * @SWG\Response(
    *   response=201,
    *   description="user created",
    * )
    *
    * @SWG\Response(
    *   response=400,
    *   description="error",
    * )
    * @SWG\Parameter(
    *     name="username",
    *     in="query",
    *     type="string",
    *     description="username field"
    * )
    * @SWG\Tag(name="clients")
    * @Security(name="Bearer")
    */
    public function addClient(Request $request, ClientRepository $repository, ValidatorInterface $validator)
    {
        $em = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent(), true);
        if (!array_key_exists("username", $data)) {
            return new JsonResponse(["error" => "username data is missing"]);
        }

        $client = $repository->findOneBy(["api" => $this->getUser(), "username" => $data["username"]]);
        

        $client = new Client();
        $client->setUsername($data["username"]);
        $client->setApi($this->getUser());
        $errors = $validator->validate($client);

        if (count($errors) > 0) {
            $errorTab = ["error" => []];
            foreach ($errors as $key => $value) {
                $errorTab["error"][$key] = $value->getMessage();
            }
            return new JsonResponse($errorTab, 400);
        }

        $em->persist($client);
        $em->flush();

        return new JsonResponse(["sucess" => "this user is now register"], 201);
    }


    /**
    *@Route("/clients/{id}", name="client_delete", methods={"DELETE"})
    * @SWG\Response(
    *   response=204,
    *   description="user delete",
    * )
    *
    * @SWG\Response(
    *   response=400,
    *   description="user doesn't exist",
    * )
    * @SWG\Tag(name="clients")
    * @Security(name="Bearer")
    */
    public function removeClient(Request $request, ClientRepository $repository, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $client = $repository->findOneBy(["api" => $this->getUser(), "id" => $id]);
        if (null === $client) {
            return new JsonResponse(["error" => "this user doesn't exist"], 400);
        }
        
        $em->remove($client);
        $em->flush();

        return new JsonResponse(["sucess" => "this user is now delete"], 204);
    }


    /**
    *@Route("/clients", name="client", methods={"GET"})
    * @SWG\Response(
    *   response=200,
    *   description="get all clients of you api",
    *   @SWG\Schema(
    *       type="array",
    *       @SWG\Items(ref=@Model(type=Client::class))
    *   )
    * )
    *
    * @SWG\Tag(name="clients")
    * @Security(name="Bearer")
    */
    public function getClients(SerializerInterface $serializer, ClientRepository $repository)
    {
        $clients = $repository->findBy(["api" => $this->getUser()]);
        $jsonObject = $serializer->serialize($clients, 'json');
        
        return (new JsonResponse($jsonObject, 200, [], true))
        ->setSharedMaxAge(3600);
    }

    /**
    *@Route("/clients/{id}", name="client_get" , methods={"GET"})
    * @SWG\Response(
    *   response=200,
    *   description="get spécific client of you api",
    *   @SWG\Schema(ref=@Model(type=Client::class))
    * )
    * @SWG\Response(
    *   response=400,
    *   description="client doesn't exist",
    * )
    *
    * @SWG\Tag(name="clients")
    * @Security(name="Bearer")
    */
    public function clientInfo(SerializerInterface $serializer, ClientRepository $repository, $id)
    {
        $client = $repository->findOneBy(["id" => $id,"api" => $this->getUser()]);
        if (null === $client) {
            return new JsonResponse(["error" => "user doesn't exist"], 400);
        }
        
        $response = (new JsonResponse($serializer->serialize($client, 'json'), 200, [], true))
        ->setSharedMaxAge(3600);

        return $response;
    }
}

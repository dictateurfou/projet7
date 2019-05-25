<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Product;
use App\Entity\Client;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 20; $i++) {
            $product = new Product();
            $product->setName('product '.$i);
            $product->setPrice(mt_rand(10, 100));
            $product->setDescription('desc'.mt_rand(10, 100));
            $product->setImageList([]);
            $manager->persist($product);
        }

        $user = new User("test");
        $password = "test";
        $user->setPassword($this->encoder->encodePassword($user, $password));
        $user->setApiKey(rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '='));
        $manager->persist($user);

        for ($i = 0; $i < 20; $i++) {
            $client = new Client();
            $client->setUsername('product '.$i);
            $client->setApi($user);
            $manager->persist($client);
        }

        $manager->flush();
    }
}

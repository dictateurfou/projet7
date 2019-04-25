<?php

namespace App\Service;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;


class EncoderJson
{
    public function __construct()
    {
       
    }

    public function encodeData($objectList){
    	$encoders = [new JsonEncoder()];
		$normalizers = [new ObjectNormalizer()];
		$serializer = new Serializer($normalizers, $encoders);

		// Serialize your object in Json
		$jsonObject = $serializer->serialize($objectList, 'json', [
		    'circular_reference_handler' => function ($object) {
		        return $object->getId();
		    },
		]);

		return $jsonObject;
    }
}


<?php


namespace App\Service;

use App\Entity\User;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Keychain; // just to make our life simpler
use Lcobucci\JWT\Signer\Rsa\Sha256; // you can use Lcobucci\JWT\Signer\Ecdsa\Sha256 if you're using ECDSA keys
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;

class JwtManager
{
    private $pemKeyDirectory;
    private $secretPhrase;

    public function __construct($pemKeyDirectory = null,$secretPhrase = null)
    {
        $this->pemKeyDirectory = $pemKeyDirectory;
        $this->secretPhrase = $secretPhrase;
    }

    public function createJwt($data = [])
    {
        $signer = new Sha256();
        $keychain = new Keychain();

        $token = (new Builder())->setIssuedAt(time()); // Configures the time that the token was issued (iat claim)

        foreach($data as $key => $value) {
            $token->set($key,$value);        
        }
        $token->sign($signer,  $keychain->getPrivateKey('file://'.$this->pemKeyDirectory.'private.pem',$this->secretPhrase));
        $token = $token->getToken();
        
        return strval($token);
    }

    public function decodeJwt($jwt){
        return (new Parser())->parse((string) $jwt);
    }

    private function verifySignature($token){
        $signer = new Sha256();
        $keychain = new Keychain();
        return $token->verify($signer, $keychain->getPublicKey('file://'.$this->pemKeyDirectory.'public.pem'));
    }

    public function isValidJwt($jwt){
        $token = $this->decodeJwt($jwt);
        $valid = false;

        if($this->verifySignature($token)){
            $valid = true;
        }

        return $valid;
    }

    public function getJwtClaim($jwt){
        $token = $this->decodeJwt($jwt);
        return $token->getClaims();
    }

}
#installation
1) configure you bdd in .env (DATABASE_URL)
2) open you console in root folder.
3) type : composer install
4) type : php bin/console doctrine:schema:update --force
5) type: php bin/console doctrine:fixtures:load
6) generate new ssh key with open ssl
7) create folder jwt in config folder
8) openssl genrsa -out config/jwt/private.pem -aes256 4096
9) openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
10) change you secret phrase in config/service.yaml (App\Service\JwtManager:)

#for get You Bearrer token
1) go in database and select user 
2) copy api_key for user test
3) go to http://localhost:8000/getJwt/{apiKey}

#optional
if you need to create new user you have api/register route (send with form-data username and password)

#doc link
go in http://localhost:8000/api/doc for api documentation

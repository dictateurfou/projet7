configure you bdd in .env (DATABASE_URL)
open you console in root folder.
type : composer install
type : php bin/console doctrine:schema:update --force
type: php bin/console doctrine:fixtures:load
generate new ssh key with open ssl
create folder jwt in config folder
openssl genrsa -out config/jwt/private.pem -aes256 4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
change you secret phrase in config/service.yaml (App\Service\JwtManager:)

for get You Bearrer token
go in database and select user 
copy api_key for user test
go to http://localhost:8000/getJwt/{apiKey}

if you need to create new user you have api/register route (send with form-data username and password)
go in http://localhost:8000/api/doc for api documentation

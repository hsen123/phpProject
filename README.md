# MQuantBackend

Server Backend and Web Frontend for the Merck MQuant Strip Scan Project

## Meta

**Jira**

https://jira.incloud.zone/projects/MM

**Wiki**

https://wiki.incloud.zone/display/MM

## Installation

**Basic**s

```bash
cp .env.dist .env
cp docker-compose.override.yml.dist docker-compose.override.yml
docker-compose build
```

### Backend

**Generate JWT keys**

```bash
mkdir config/jwt
openssl genrsa -out config/jwt/private.pem 4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```

**Initialize database**

```bash
docker-compose run --rm php composer install
docker-compose run --rm php php bin/console doctrine:database:drop --if-exists --force
docker-compose run --rm php php bin/console doctrine:database:create --if-not-exists
docker-compose run --rm php php bin/console doctrine:migrations:migrate --no-interaction
docker-compose run --rm php php bin/console app:add-faq
docker-compose up -d
docker-compose run --rm php php bin/console doctrine:fixtures:load --no-interaction
```

### Frontend

**Compile assets once**

```bash
docker-compose run --rm node npm install
docker-compose run --rm node npm run build-dev-assets
```

**Recompile assets automatically when files change**

```bash
docker-compose run --rm node npm run watch
```

#### Linting

Before you commit, make sure your code has the right coding styleguide and execute following command:

**Backend Linting**

```bash
docker-compose run --rm php php-cs-fixer fix
```

**Frontend Linting**

```bash
docker-compose run --rm node npm run lint
```

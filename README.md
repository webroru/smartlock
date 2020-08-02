# smartlock #

## Installation ##

### Docker ###
1. Install [Docker](https://docs.docker.com/engine/installation/linux/ubuntu/)
2. Install [Docker Compose](https://docs.docker.com/compose/install/)
3. Run the project: `docker-compose up -d`
4. Instal dependencies: `docker-compose php exec composer install`
5. `cp .env.dev .env`
6. Set variables in the .env
7. Download firebase-credentials.json from https://console.firebase.google.com/

### Production ###
1. Set variables in the .env
2. Download firebase-credentials.json from https://console.firebase.google.com/
3. Add to cron:

```
0 0 * * * php -f app.php expiredPasscodesRemover >> logs/cron.log 2>&1
0 0 * * * php -f app.php checkDelayedBooking >> logs/cron.log 2>&1
* * * * * php -f app.php reservationChecker >> logs/cron.log 2>&1
```

## Tests ##
Run all tests `./composer.phar test`

Run specific test:  `./vendor/bin/phpunit --filter [test method] [path to test class]`, example: `./vendor/bin/phpunit --filter testGetCheckInDate tests/ParserTest.php`

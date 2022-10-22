# smartlock #

## Installation ##

### Docker ###
1. Install [Docker](https://docs.docker.com/engine/installation/linux/ubuntu/)
2. Install [Docker Compose](https://docs.docker.com/compose/install/)
3. Run the project: `docker-compose up -d`
4. Instal dependencies: `docker-compose run php composer install`
5. Import MySql table: `cat ./migrations/*.sql | mysql -uroot -h 0.0.0.0 smartlock`

### Select data storage ###
You can use MySql or Google Firebase.
— For Firebase: Download firebase-credentials.json from https://console.firebase.google.com/
— For Mysql Import MySql table: `mysql -uroot -h 0.0.0.0 smartlock < 1_init.sql`

### Production ###
1. Set variables in the .env
2. Add to cron:

```
0 0 * * * php -f app.php expiredPasscodesRemover >> logs/cron.log 2>&1
0 0 * * * php -f app.php checkDelayedBooking >> logs/cron.log 2>&1
* * * * * php -f app.php reservationChecker >> logs/cron.log 2>&1
```

## Configuration ##
1. `cp .env.dev .env`
2. Set variables in the .env

## API
```shell
curl "http://127.0.0.1/api/create" \
-H 'Accept: application/json' \
-H "Content-Type: application/json" \
-H "Authorization: Bearer ${BEDS24_DIGEST_TOKEN}" \
--request POST -d '{"order_id":"42","checkindate":"2000-01-01","checkoutdate":"2100-01-01","guestname":"John Doe","property":"123","room":"111"}'
```

## Tests ##
Run all tests `docker-compose run php ./composer.phar test`

Run specific test:  `docker-compose run php ./vendor/bin/phpunit --filter [test method] [path to test class]`, example: `docker-compose run php ./vendor/bin/phpunit --filter testGetCheckInDate tests/ParserTest.php`

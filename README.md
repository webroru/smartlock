# smartlock #

## Installation ##
1. `./composer.phar install`
2. `cp .env.dev .env`
3. Set variables in the .env
4. Download firebase-credentials.json from https://console.firebase.google.com/
5. Add to cron:
```
0 0 * * * php -f app.php expiredPasscodesRemover >> logs/cron.log 2>&1
* * * * * php -f app.php reservationChecker >> logs/cron.log 2>&1
```

## Tests ##
Run all tests `./composer.phar test`

Run specific test:  `./vendor/bin/phpunit --filter [test method] [path to test class]`, example: `./vendor/bin/phpunit --filter testGetCheckInDate tests/ParserTest.php` 
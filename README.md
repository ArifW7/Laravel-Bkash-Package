Laravel bKash Payment Gateway Package

Installation:
1. Install via Composer
   composer require arifw7/bkash:dev-main

3. Publish Config & Views

php artisan vendor:publish --provider="ArifW7\Bkash\BkashServiceProvider" --tag=config
php artisan vendor:publish --provider="ArifW7\Bkash\BkashServiceProvider" --tag=views

5. Add Environment Variables

Add these to your .env file:
BKASH_BASE_URL=https://tokenized.sandbox.bka.sh/v1.2.0-beta

SANDBOX=true // For sandbox and SANDBOX=false for live

BKASH_USERNAME=01770618567

BKASH_PASSWORD=D7DaC<*E*eG

BKASH_APP_KEY=0vWQuCRGiUX7EPVjQDr0EUAYtc

BKASH_APP_SECRET=jcUNPBgbcqEDedNKdvE4G1cAK7D3hCjmJccNPZZBq96QIxxwAMEx

7. Migration Database:
   
php artisan migrate

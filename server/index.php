<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Stripe\Stripe;

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = new \Slim\App;

$app->add(function ($request, $response, $next) {
  Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
  return $next($request, $response);
});

$app->get('/', function (Request $request, Response $response) {
  return $response->write(file_get_contents("../client/index.html"));
});

$app->post('/create-checkout-session', function (Request $request, Response $response) {
  $session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => [[
      'price_data' => [
        'currency' => 'usd',
        'product_data' => [
          'name' => 'T-shirt',
        ],
        'unit_amount' => 2000,
      ],
      'quantity' => 1,
    ]],
    'mode' => 'payment',
    'success_url' => 'https://example.com/success',
    'cancel_url' => 'https://example.com/cancel',
  ]);

  return $response->withJson([ 'id' => $session->id ])->withStatus(200);
});

$app->run();
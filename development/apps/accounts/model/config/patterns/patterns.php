<?php
$patterns = [
  'name' => [
    'regular' => '@^[A-Z][a-z]+$@',
    'function' => null,
  ],
  'phone' => [
    'regular' => '@[5-9][0-9]{8}\b@',
    'function' => function ($phone) {
      return "+380".substr($phone, strlen($phone) - 9);
    }
  ] 
];

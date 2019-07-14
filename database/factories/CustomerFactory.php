<?php

use App\Customer;
use Faker\Generator as Faker;

$factory->define(Customer::class, function (Faker $faker) {
    $phoneNumber = $faker->randomNumber(8, true);
    return [
        'name'                   => $faker->name,
        'reg_type'               => 'mobile',
        'mobile_number'          => '+4207' . $phoneNumber,
        'mobile_country_prefix'  => '420',
        'mobile_national_number' => '7' . $phoneNumber,
        'account_kit_id'         => $faker->creditCardNumber(),
    ];
});

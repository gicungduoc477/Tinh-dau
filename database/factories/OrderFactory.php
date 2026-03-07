<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'total_price' => $this->faker->numberBetween(10000, 500000),
            'status' => 'pending',
            'payment_method' => 'cod',
            'shipping_method' => 'fast',
            'shipping_fee' => 25000,
        ];
    }
}

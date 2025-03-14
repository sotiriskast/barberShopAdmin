<?php


namespace App\Modules\Barber\Database\Factories;

use App\Modules\Barber\Models\Barber;
use App\Modules\Shop\Models\Shop;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BarberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Barber::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory()->create(['role' => 'barber'])->id,
            'shop_id' => Shop::factory(),
            'title' => $this->faker->randomElement(['Senior Barber', 'Master Barber', 'Junior Barber', 'Hair Stylist']),
            'bio' => $this->faker->paragraph(3),
            'years_experience' => $this->faker->numberBetween(1, 20),
            'instagram_handle' => $this->faker->optional(0.7)->userName,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the barber is inactive.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }

    /**
     * Indicate that the barber is experienced.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function experienced()
    {
        return $this->state(function (array $attributes) {
            return [
                'years_experience' => $this->faker->numberBetween(5, 30),
                'title' => 'Master Barber',
            ];
        });
    }

    /**
     * Indicate that the barber is a beginner.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function beginner()
    {
        return $this->state(function (array $attributes) {
            return [
                'years_experience' => $this->faker->numberBetween(0, 2),
                'title' => 'Junior Barber',
            ];
        });
    }
}

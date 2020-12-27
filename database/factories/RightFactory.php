<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Models\Group;
use App\Models\Right;
use Illuminate\Database\Eloquent\Factories\Factory;

class RightFactory extends Factory
{
    const FIELDS = [
        Post::class => ['title', 'body'],
        User::class => ['name', 'email', 'password'],
        Group::class => ['slug', 'name', 'description'],
    ];

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Right::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $model = $this->faker->randomElement([
            Post::class, User::class, Group::class
        ]);

        $fields = self::FIELDS[$model];
        $cnt = count($fields);

        return [
            'model' => $model,

            'create' => $this->faker->optional(0.7)
                ->randomElements($fields, mt_rand(1, $cnt))
                ?: $this->faker->optional()->passthrough(['*']),

            'read' => $this->faker->optional(0.7)
                ->randomElements($fields, mt_rand(1, $cnt))
                ?: $this->faker->optional()->passthrough(['*']),

            'update' => $this->faker->optional(0.7)
                ->randomElements($fields, mt_rand(1, $cnt))
                ?: $this->faker->optional()->passthrough(['*']),

            'delete' => $this->faker->boolean,
        ];
    }
}

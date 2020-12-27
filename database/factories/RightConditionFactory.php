<?php

namespace Database\Factories;

use App\Enums\OperatorEnum;
use App\Models\RightCondition;
use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Enum\Laravel\Faker\FakerEnumProvider;

class RightConditionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RightCondition::class;

    public function configure()
    {
        FakerEnumProvider::register();

        return $this;
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        do {
            $enum = $this->faker->randomEnum(OperatorEnum::class);
        } while ($enum->isScope());

        return [
            'field' => $this->faker->randomElement([
                'id', 'slug', 'name', 'description', 'email',
                'password', 'model', 'created_at', 'updated_at'
            ]),
            /** @var OperatorEnum $enum */
            'operator' => $enum,
            'value' => $this->faker->word,
        ];
    }
}

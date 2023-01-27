<?php

namespace Database\Factories;

use App\Models\Feeling;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class FeelingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Feeling::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $feelings = [
            'anxious',
            'hard',
            'tired',
            'sad',
            'angry',
            'kuyashi',
            'lethargic',
            'moyamoya',
            'glad',
            'fun',
            'calm',
            'happy',
            'wakuwaku',
        ];
        $feeling = $feelings[rand(0, count($feelings) - 1)];
        return [
            'feeling_type' => $feeling,
            'date' => new Carbon('-1 weeks'),
            'time' => date('H:i:s'),
        ];
    }
}

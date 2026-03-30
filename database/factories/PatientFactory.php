<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_rm' => 'RM-' . $this->faker->unique()->numerify('#####'),
            'nama' => $this->faker->name(),
            'tanggal_lahir' => $this->faker->date(),
            'jenis_kelamin' => $this->faker->randomElement(['L', 'P']),
            'no_hp' => $this->faker->phoneNumber(),
            'no_bpjs' => '000' . $this->faker->unique()->numerify('#####'),
            'email' => $this->faker->safeEmail(),
            'alamat' => $this->faker->address(),
            'riwayat_penyakit' => $this->faker->sentence(10),
        ];
    }
}

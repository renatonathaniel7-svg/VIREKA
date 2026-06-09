<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Verification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory: VerificationFactory
 *
 * Data verifikasi AI yang realistis.
 * ai_extracted_data mensimulasikan output Gemini Vision.
 */
class VerificationFactory extends Factory
{
    protected $model = Verification::class;

    public function definition(): array
    {
        $referenceType = $this->faker->randomElement(['income', 'expense']);
        $status        = $this->faker->randomElement([
            'verified', 'verified', 'verified',  // ~60% verified
            'flagged', 'pending',                 // ~40% lainnya
        ]);

        $amount     = $this->faker->numberBetween(50000, 5000000);
        $confidence = match($status) {
            'verified' => $this->faker->randomFloat(2, 0.87, 0.99),
            'flagged'  => $this->faker->randomFloat(2, 0.40, 0.75),
            'pending'  => null,
            default    => $this->faker->randomFloat(2, 0.60, 0.90),
        };

        // Simulasi output Gemini Vision
        $aiExtractedData = $status !== 'pending' ? [
            'amount'             => $amount + $this->faker->numberBetween(-5000, 5000), // sedikit delta
            'date'               => now()->subDays($this->faker->numberBetween(1, 30))->format('Y-m-d'),
            'source'             => $this->faker->randomElement(['BCA', 'BRI', 'Mandiri', 'BNI', 'DANA', 'GoPay', 'OVO']),
            'raw_text'           => 'Transfer Dana sebesar Rp ' . number_format($amount, 0, ',', '.'),
            'extraction_method'  => $this->faker->randomElement(['gemini', 'gemini', 'tesseract']),
        ] : null;

        return [
            'user_id'           => User::factory(),
            'reference_type'    => $referenceType,
            'reference_id'      => $this->faker->numberBetween(1, 50),
            'screenshot_path'   => 'verifications/screenshot_' . $this->faker->uuid() . '.jpg',
            'ai_extracted_data' => $aiExtractedData,
            'ai_confidence'     => $confidence,
            'status'            => $status,
            'flag_reason'       => $status === 'flagged'
                ? $this->faker->randomElement([
                    'delta_exceeded: amount difference > 5%',
                    'low_confidence: AI confidence below threshold',
                    'date_mismatch: screenshot date differs from input',
                    'unreadable: screenshot quality too low',
                ])
                : null,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'        => 'verified',
            'ai_confidence' => $this->faker->randomFloat(2, 0.88, 0.99),
        ]);
    }

    public function flagged(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'        => 'flagged',
            'ai_confidence' => $this->faker->randomFloat(2, 0.40, 0.74),
            'flag_reason'   => 'delta_exceeded: amount difference > 5%',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'            => 'pending',
            'ai_confidence'     => null,
            'ai_extracted_data' => null,
        ]);
    }
}

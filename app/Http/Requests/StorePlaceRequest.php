<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'environment_id' => ['required', 'exists:environments,id'],

            'nom' => ['required', 'string', 'max:150'],

            'description' => ['nullable', 'string', 'max:500'],

            'latitude' => ['required', 'numeric'],

            'longitude' => ['required', 'numeric'],

            'rayon_validation' => ['required', 'integer', 'min:1'],

            'ordre' => ['required', 'integer', 'min:0'],

            'popularite' => ['required', 'integer', 'between:1,5'],

            'recommandation' => ['nullable', 'array'],
            'recommandation.*.nom' => ['required_with:recommandation', 'string', 'max:150'],
            'recommandation.*.description' => ['nullable', 'string', 'max:255'],

            'ressource' => ['nullable', 'string', 'max:30'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $recommandations = collect($this->input('recommandation', []))
            ->filter(fn ($item) => filled($item['nom'] ?? null))
            ->values()
            ->all();

        $this->merge([
            'recommandation' => $recommandations ?: null,
            'ressource' => filled($this->ressource) ? $this->ressource : null,
        ]);
    }
}
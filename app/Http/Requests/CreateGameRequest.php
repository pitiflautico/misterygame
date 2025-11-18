<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateGameRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isCreator();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'prompt' => 'required|string|min:10|max:2000',
            'game_type' => 'required|in:murder,mystery,liminal,investigation,horror,escape,interactive_movie,guided_adventure',
            'min_players' => 'nullable|integer|min:1|max:20',
            'max_players' => 'nullable|integer|min:1|max:20|gte:min_players',
            'duration' => 'nullable|integer|min:15|max:480',
            'difficulty' => 'nullable|in:easy,medium,hard,expert',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'prompt.required' => 'Please provide a description for your game',
            'prompt.min' => 'The game description must be at least 10 characters',
            'game_type.required' => 'Please select a game type',
            'game_type.in' => 'Invalid game type selected',
            'max_players.gte' => 'Maximum players must be greater than or equal to minimum players',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    public function rules(): array
    {
        return [
            'game_id' => 'required|exists:games,id',
            'max_players' => 'nullable|integer|min:1|max:20',
            'mode' => 'nullable|in:solo,group',
            'is_public' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'game_id.required' => 'Please select a game',
            'game_id.exists' => 'The selected game does not exist',
            'max_players.max' => 'Maximum 20 players allowed per session',
        ];
    }
}

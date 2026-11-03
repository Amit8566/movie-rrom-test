<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaybackActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:play,pause,stop'],
            'current_time' => ['required', 'numeric', 'min:0'],
        ];
    }
}

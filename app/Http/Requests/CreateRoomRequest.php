<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'video_url' => ['required', 'url', 'max:500'],
            'host_name' => ['required', 'string', 'max:50'],
        ];
    }
}

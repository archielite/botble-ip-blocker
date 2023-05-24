<?php

namespace ArchiElite\IpBlocker\Http\Requests;

use Botble\Support\Http\Requests\Request;

class UpdateIpBlockerRequest extends Request
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'ip_addresses' => json_decode($this->input('ip_addresses'), true),
        ]);
    }

    public function rules(): array
    {
        return [
            'ip_addresses' => ['required', 'array'],
            'ip_addresses.*.value' => ['required', 'ip'],
        ];
    }

    public function attributes(): array
    {
        return [
            'ip_addresses.*.value' => __('IP address'),
        ];
    }
}

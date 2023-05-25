<?php

namespace ArchiElite\IpBlocker\Http\Requests;

use Botble\Support\Http\Requests\Request;

class UpdateSettingsRequest extends Request
{
    protected function prepareForValidation(): void
    {
        $ipAddresses = $this->input('ip_addresses');

        $this->merge([
            'ip_addresses' => $ipAddresses ? json_decode($ipAddresses, true) : [],
        ]);
    }

    public function rules(): array
    {
        return [
            'ip_addresses' => ['sometimes', 'array'],
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

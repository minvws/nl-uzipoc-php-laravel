<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    /**
     * Get the host patterns that should be trusted.
     *
     * @return array
     */
    public function hosts(): array
    {
        return [
            $this->onlyAllowApplicationUrl(),
        ];
    }

    /**
     * Normally we use allSubdomainsOfApplicationUrl() of Laravel.
     * We currently do not want to allow all subdomains and only allow the application url.
     * Prevents Host Header Injection - WSTG-INPV-17
     */
    protected function onlyAllowApplicationUrl(): ?string
    {
        if ($host = parse_url(config('app.url'), PHP_URL_HOST)) {
            return '^' . preg_quote($host) . '$';
        }

        return null;
    }
}

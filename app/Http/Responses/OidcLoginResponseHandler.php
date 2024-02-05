<?php

declare(strict_types=1);

namespace App\Http\Responses;

use MinVWS\OpenIDConnectLaravel\Http\Responses\LoginResponseHandlerInterface;
use Symfony\Component\HttpFoundation\Response;

class OidcLoginResponseHandler implements LoginResponseHandlerInterface
{
    /**
     * @param object{
     *      relations: array<int, object{entity_name: string, ura: string, roles: string[]}>,
     *      initials: ?string,
     *      surname: ?string,
     *      surname_prefix: ?string,
     *      uzi_id: string,
     *      loa_uzi: string,
     *      loa_authn: string
     *  } $userInfo
     */
    public function handleLoginResponse(object $userInfo): Response
    {
        return response()
            ->redirectTo(route('user'))
            ->with('user', $userInfo);
    }
}

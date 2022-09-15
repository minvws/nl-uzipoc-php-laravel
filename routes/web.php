<?php

use App\Services\Oidc\OidcService;
use Illuminate\Support\Facades\Route;
use Jumbojett\OpenIDConnectClient;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function() {
    $oidc = new OpenIDConnectClient(provider_url: config('oidc.issuer'));
    $oidc->setClientID(config('oidc.client_id'));
    if (!empty(config('oidc.client_secret'))) {
        $oidc->setClientSecret(config('oidc.client_secret'));
    }
    $oidc->setCodeChallengeMethod('S256');
    $oidc->setRedirectURL(route('login'));

    foreach (config('uzi.additional_scopes') as $scope) {
        $oidc->addScope($scope);
    }

    // Redirect to login at max
    $oidc->authenticate();

    // After login, this is executed
    // Currently we cannot use the $oidc->requestUserInfo() method because we use JWE

    // Get the access token
    $accessToken = $oidc->getAccessToken();

    // Custom OIDC service to request user info
    $oidcService = new OidcService(
        issuer: config('uzi.issuer'),
        decryptionKeyPath: config('uzi.decryption_key_path'),
    );

    // Get user information
    $userInfo = $oidcService->requestUserInfo($accessToken);

    return redirect()
        ->route('user')
        ->with('user', $userInfo);
})->name('login');

Route::get('/user', function() {
    if (!session()->has('user')) {
        return redirect()->route('login');
    }

    dump(session('user'));
})->name('user');
<?php

namespace Linkvalue\LVConnect\Symfony\SDK\Service;


use League\OAuth2\Client\Provider\GenericProvider;
use Linkvalue\LVConnect\Symfony\SDK\Provider\LVConnectProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;


class LVConnectClient
{

    /** @var LVConnectProvider */
    private $lvConnectProvider;

    /** @var @var Session */
    private $session;

    const SKO2_STATE = 'oauth2_state';
    const SKO2_ACCESS_TOKEN = 'oauth2_access_token';
    const SKO2_USER = 'oauth2_user_profile';

    public function __construct(LVConnectProvider $lvConnectProvider, Session $session)
    {
        $this->lvConnectProvider = $lvConnectProvider;
        $this->session = $session;
    }

    protected function authenticate(Request $request): void
    {
        try {

            // Try to get an access token using the authorization code grant.
            $accessToken = $this->lvConnectProvider->getAccessToken(
                'authorization_code',
                [ 'code' => $request->query->get('code') ]
            );

            $this->session->set(static::SKO2_ACCESS_TOKEN, $accessToken);

        } catch (\Exception $e) {
            throw new \RuntimeException("Access token processing error.");
        }
    }

    protected function tokenResponse(): Response
    {
        if(!$this->session->has(static::SKO2_ACCESS_TOKEN)) {
            throw new \RuntimeException("Access token processing error.");
        }

        $body = $this->session->get(static::SKO2_ACCESS_TOKEN);

        return new Response(
            json_encode($body,JSON_UNESCAPED_UNICODE),
            JsonResponse::HTTP_OK,
            [
                'Content-Type' => 'application/json',
                'Access-Control-Allow-Origin' => '*',
            ]
        );
    }

    public function authenticateResponse(Request $request): Response
    {
        // no token in session, no code given
        if (
            !$this->session->has(static::SKO2_ACCESS_TOKEN)
            &&
            !$request->query->has('code')
        ) {
            return $this->redirectResponse($request);
        }
        // token in session and code given
        elseif (
            $this->session->has(static::SKO2_ACCESS_TOKEN)
            &&
            $request->query->has('code')
        ) {
            $this->session->remove(static::SKO2_ACCESS_TOKEN);
            return $this->redirectResponse($request);
        }
        // token in session
        elseif(
            $this->session->has(static::SKO2_ACCESS_TOKEN)
        ) {
            return $this->tokenResponse();
        }
        // code given
        else
        {
            $this->authenticate($request);
            return $this->tokenResponse();
        }
    }


    private function redirectResponse(Request $request)
    {
        $options = [];
        if($request->query->has('redirect_uri')) {
            $options['redirect_uri'] = $request->query->get('redirect_uri');
        }

        $redirectHandler = function (string $authorizationUrl, GenericProvider $genericProvider) {
            return new Response(
                json_encode(null, JSON_UNESCAPED_UNICODE),
                JsonResponse::HTTP_PERMANENTLY_REDIRECT,
                [
                    'Content-Type' => 'application/json',
                    'Access-Control-Allow-Origin' => '*',
                    'Location' => $authorizationUrl
                ]
            );
        };

        return $this->lvConnectProvider->authorize($options, $redirectHandler);
    }


}

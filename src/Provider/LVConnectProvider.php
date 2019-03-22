<?php

namespace Linkvalue\LVConnect\Symfony\SDK\Provider;

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use Linkvalue\LVConnect\Symfony\SDK\Model\BasePartner;


class LVConnectProvider extends GenericProvider
{
    public function getResourceOwner(AccessToken $token) : BasePartner
    {
        $genericResourceOwner = parent::getResourceOwner($token);

        $partner_array = $genericResourceOwner->toArray();
        $partner_array['credentials'] = $token;

        $partner = new BasePartner($partner_array);

        return $partner;
    }

    protected function getAuthorizationParameters(array $options)
    {
        $auth_options = parent::getAuthorizationParameters($options);

        // not managed by LVConnect
        unset($auth_options['approval_prompt']);

        return $auth_options;
    }



}

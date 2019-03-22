<?php


namespace Linkvalue\LVConnect\Symfony\SDK\Model;


use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;
use League\OAuth2\Client\Tool\GuardedPropertyTrait;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class BasePartner
 *
 * This base class is made in order to be extended by any Symfony service
 * using LVConnect authentication.
 *
 * @package Linkvalue\LVConnect\Symfony\SDK\Model
 */
class BasePartner implements UserInterface, ResourceOwnerInterface
{
    use ArrayAccessorTrait;
    use GuardedPropertyTrait;

    /** var UUID */
    private $id;

    /** var AccessToken */
    private $credentials;

    /** var string[] */
    private $roles;


    /****************************************
     * TODO : lv partner specific attributes
     ****************************************/

    /** var string */
    private $firstName;

    /** @var var string */
    private $lastName;

    /** var string */
    private $email;

    /** var array */
    private $tags;

    /** var boolean */
    private $needPasswordChange;

    /** var string */
    private $createdAt;

    /** var string */
    private $profilePictureUrl;



    /******************************
     * methods from UserInterface
     ******************************/

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        throw new \RuntimeException("Not implemented.");
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function eraseCredentials()
    {
        $this->credentials = null;
    }

    public function getSalt()
    {
        throw new \RuntimeException("Not implemented.");
    }


    /***************************************
     * methods from ResourceOwnerInterface
     ***************************************/

    public function getId()
    {
        return $this->id;
    }

    public function toArray()
    {
        throw new \RuntimeException("Not implemented.");
    }


    /*********************
     * Specific methods
     *********************/

    public function __construct(array $partner)
    {
        $this->fillProperties($partner);
    }

}

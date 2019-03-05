<?php

declare(strict_types=1);

namespace App\User;

use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Facades\Crypt;

class Model extends Eloquent implements AuthorizableContract, AuthenticatableContract
{
    use Authorizable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'name',
        'salt',
        'verifier',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'salt',
        'verifier',
    ];

    /**
     * Decrypt the verifier when gotten.
     *
     * @return string
     */
    public function getVerifierAttribute()
    {
        try {
            return Crypt::decryptString(array_get($this->attributes, 'verifier'));
        } catch (DecryptException $exception) {
        }
    }

    /**
     * Encrypt the verifier when set.
     *
     * @param string
     */
    public function setVerifierAttribute($verifier)
    {
        array_set($this->attributes, 'verifier', Crypt::encryptString($verifier));
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'email';
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->verifier;
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string|null
     */
    public function getRememberToken()
    {
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param string $value
     */
    public function setRememberToken($value)
    {
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }
}

<?php

namespace Fcker\Application\Models;

use Fcker\Framework\Core\Model;

class UserModel extends Model
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';
    protected array $fillable = ['name', 'email', 'password'];
    protected array $hidden = [];

    public function __construct()
    {
        parent::__construct();
    }
}

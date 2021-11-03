<?php

namespace App\Models;

use Hamcrest\Thingy;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    const IS_ACTIVE = 1;
    const IS_BANNED = 0;


    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public static function add($fields)
    {
        $user = new static;
        $user->fill($fields);
        $user->password = bcrypt($fields['password']);
        $user->save();

        return $user;
    }

    public function edit($fields)
    {
        $this->fill($fields);
        $this->password = bcrypt($fields['password']);
        $this->save();
    }

    public function remove()
    {
        Storage::delete('uploads/ ' . $this->image);
        $this->delete();
    }

    public function uploadAvatar($image)
    {
        if ($image == null) { return; }

        Storage::delete('uploads/ ' . $this->image);
        $fileName = str_random(10) . '.' . $image->extension();
        $image = saveAs('uploads',  $fileName);
        $this->image = $fileName;
        $this->save();
    }

    public function getImage()
    {
        if ($this->image == null)
        {
            return '/img/no-user-image.png';
        }
        else
        {
            return '/uploads/' . $this->image;
        }
    }

    public function makeAdmin()
    {
        $this->is_admin = 1;
    }

    public function makeNormal()
    {
        $this->is_admin = 0;
    }
    public function toggleAdmin($val)
    {
        if ($val == null)
        {
            return $this->makeNormal();
        }
        else
        {
            return $this->makeAdmin();
        }
    }

    public function ban()
    {
        $this->status = User::IS_BANNED;
        $this->save();
    }

    public function unban()
    {
        $this->status = User::IS_ACTIVE;
        $this->save();
    }

    public function toggleBan($val)
    {
        if ($val == null)
        {
            return $this->unban();
        }
        else
        {
            return $this->ban();
        }
    }
}

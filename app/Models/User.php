<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Class App\Models\User
 *
 * @property mixed id
 * @property mixed name
 * @property mixed email
 * @property mixed email_verified_at
 * @property mixed password
 * @property mixed remember_token
 * @property mixed created_at
 * @property mixed updated_at
 * @property mixed sub
 *
 * @method static \App\Models\User find($id)
 * @method static \App\Models\User create(array $attributes = [])
 * @method static \App\Models\User updateOrCreate(array $attributes, array $values = [])
 * @method static \App\Models\User firstOrCreate(array $attributes, array $values = [])
 * @method static \App\Models\User firstOrNew(array $attributes, array $values = [])
 * @method static \App\Models\User update(array $attributes = [], array $options = [])
 */

class User extends Authenticatable {
  use HasApiTokens, HasFactory, Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'email',
    'password',
    'sub',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
  ];
}

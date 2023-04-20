<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class App\Models\Token
 *
 * @property mixed id
 * @property mixed user_id
 * @property mixed access_token
 * @property mixed refresh_token
 * @property mixed expires_at
 * @property mixed created_at
 * @property mixed updated_at
 *
 * @method static \App\Models\Token find($id)
 * @method static \App\Models\Token create(array $attributes = [])
 * @method static \App\Models\Token updateOrCreate(array $attributes, array $values = [])
 * @method static \App\Models\Token firstOrCreate(array $attributes, array $values = [])
 * @method static \App\Models\Token firstOrNew(array $attributes, array $values = [])
 * @method static \App\Models\Token update(array $attributes = [], array $options = [])
 */

class Token extends Model {
  use HasFactory;
}

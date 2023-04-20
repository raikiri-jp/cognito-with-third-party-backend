<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class App\Models\LoginHistory
 *
 * @property mixed id
 * @property mixed user_id
 * @property mixed ip_address
 * @property mixed login_at
 * @property mixed created_at
 * @property mixed updated_at
 *
 * @method static \App\Models\LoginHistory find($id)
 * @method static \App\Models\LoginHistory create(array $attributes = [])
 * @method static \App\Models\LoginHistory updateOrCreate(array $attributes, array $values = [])
 * @method static \App\Models\LoginHistory firstOrCreate(array $attributes, array $values = [])
 * @method static \App\Models\LoginHistory firstOrNew(array $attributes, array $values = [])
 * @method static \App\Models\LoginHistory update(array $attributes = [], array $options = [])
 */

class LoginHistory extends Model {
  use HasFactory;
}

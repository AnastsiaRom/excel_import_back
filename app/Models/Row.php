<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int     $id
 * @property int     $excel_id
 * @property string  $name
 * @property string  $date
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 *
 */

class Row extends Model
{

    protected $fillable = [
        'excel_id',
        'name',
        'date',
    ];
}

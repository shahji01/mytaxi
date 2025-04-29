<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [ 
        'name', 
        'longitude',
        'latitude',
        'latitude_north',
        'latitude_south',
        'longitude_east',
        'longitude_west',
        'additional_kilometer',
        'no_of_allow_drivers_in_save_zone',
        'no_of_minuts_remove_queue_out_save_zone',
        'added_by',
        'status' ];

    public function scopemyLocation($query)
    {
        $user = auth()->user();

        if($user->hasAnyRole(['driver','rider'])){
            $query = $query->where('added_by', $user->id);
        } else {
            $query = $query->where('added_by', $user->id);
        }

        return $query;
    }
    
}

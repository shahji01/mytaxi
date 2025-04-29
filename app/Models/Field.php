<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    use HasFactory;
    // Custom Table Name
    protected $table = 'document_fields';
    protected $guarded = [];
}

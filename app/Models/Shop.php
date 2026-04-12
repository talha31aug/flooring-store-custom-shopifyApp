<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    // Fillable fields to protect against mass assignment vulnerabilities
    protected $fillable = [
        'shopify_domain',
        'shopify_token',
    ];

    // Optionally define the table name if it's different
    // protected $table = 'shops'; 
}

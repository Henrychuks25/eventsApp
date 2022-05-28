<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $guarded = [];

    // get categories
    public function get_categories()
    {
        return Category::where(['status' => 1])->get()->toArray();
    }

    // get event category
    public function get_event_category($id = null)
    {
        return Category::where(['id' => $id])->first()->toArray();
    }
}

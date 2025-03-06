<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Form extends Model
{
    use SoftDeletes;

    protected $table = 'form';

    public function book()
    {
        return $this->belongsTo(Book::class,'book_id');
    }

    public function bookSeason()
    {
        return $this->belongsTo(BookSeason::class,'season_id');
    }

    public function bookCategory()
    {
        return $this->belongsTo(BookCategory::class,'category_id');
    }
    public function question()
    {
        return $this->hasMany(Question::class,'form_id');
    }


}

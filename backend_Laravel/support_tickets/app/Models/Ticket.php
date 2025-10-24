<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Ticket extends Model
{
    use HasFactory;

    protected $fillable = ['titre', 'description', 'statut', 'user_id', 'deadline'];
    
    protected $casts = [
        'deadline' => 'date',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function commentaires() {
        return $this->hasMany(Commentaire::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referentiel extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'photo',
        'statut',
    ];

    // Ajoutez d'autres relations ou méthodes si nécessaire
    public function competences()
{
    return $this->hasMany(Competence::class);
}

}

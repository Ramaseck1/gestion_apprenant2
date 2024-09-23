<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competence extends Model
{
    use HasFactory;

    protected $fillable = [
        'referentiel_id',
        'nom',
        'description',
        'duree_acquisition',
        'type',
    ];

    // Définir la relation avec le référentiel
    public function referentiel()
    {
        return $this->belongsTo(Referentiel::class);
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompetencesTable extends Migration
{
    public function up()
    {
        Schema::create('competences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referentiel_id')->constrained()->onDelete('cascade'); // Lien avec le référentiel
            $table->string('nom');
            $table->text('description')->nullable();
            $table->integer('duree_acquisition'); // Durée d'acquisition en jours ou heures
            $table->string('type'); // Type: Back-end ou Front-End
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('competences');
    }
}

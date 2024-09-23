<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferentielsTable extends Migration
{
    public function up()
    {
        Schema::create('referentiels', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Code unique
            $table->string('libelle')->unique(); // Libellé unique
            $table->text('description')->nullable();
            $table->string('photo')->nullable();
            $table->enum('statut', ['Actif', 'Inactif', 'Archiver'])->default('Actif'); // Statut
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('referentiels');
    }
}

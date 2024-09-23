<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->bigIncrements('id')->change(); // ou $table->increments('id')->change();
            $table->string('firebase_uid')->unique()->nullable();
            $table->string('nom');
            $table->string('prenom');
            $table->string('adresse')->nullable(); // Peut être null si pas nécessaire
            $table->string('telephone')->unique(); // Numéro de téléphone unique
            $table->string('email')->unique(); // Email unique
            $table->string('photo')->nullable(); // Lien de la photo (peut être null)
            $table->unsignedBigInteger('role_id'); // Clé étrangère vers la table roles
            $table->enum('statut', ['Bloquer', 'Actif'])->default('Actif'); // Statut de l'utilisateur
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');         
            $table->timestamp('email_verified_at')->nullable(); // Vérification email
            $table->string('password'); // Mot de passe
            $table->rememberToken(); // Token pour "Remember me"
            $table->timestamps(); // Dates de création et de mise à jour
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->bigInteger('id')->change(); // Revenir à bigint si nécessaire

        });
    }
};

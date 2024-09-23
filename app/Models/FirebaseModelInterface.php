<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

interface FirebaseModelInterface 
{
    public function createUser(array $data);
    public function checkUnique($field, $value);
    public function getAuth();
}
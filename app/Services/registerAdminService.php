<?php
namespace App\Services;

use App\Facades\UploadFacade;
use App\Repository\registerRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class registerAdminService implements RegisterAdminServiceInterface
{
    protected $userRepository;

    public function __construct(registerRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function registerUser(array $data)
    {
        // Hash du mot de passe
        $data['password'] = Hash::make($data['password']);
        
    
        // Appel du repository pour créer l'utilisateur
        return $this->userRepository->create($data);
    }
}

<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

#[Signature('create:admin')]
#[Description('Crear un usuario administrador')]
class CreateAdminUser extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->ask('¿Cuál es el nombre del administrador?');
        $email = $this->ask('¿Cuál es el email del administrador?');
        
        // Verificar que el email no exista
        if (User::where('email', $email)->exists()) {
            $this->error('El email ya existe en la base de datos.');
            return 1;
        }

        $password = $this->secret('¿Cuál es la contraseña?');
        $password_confirm = $this->secret('Confirma la contraseña:');

        if ($password !== $password_confirm) {
            $this->error('Las contraseñas no coinciden.');
            return 1;
        }

        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
                'is_active' => true,
            ]);

            $user->assignRole('admin');

            $this->info("¡Administrador '$name' creado exitosamente!");
            $this->info("Email: $email");
            return 0;
        } catch (\Exception $e) {
            $this->error('Error al crear el administrador: ' . $e->getMessage());
            return 1;
        }
    }
}

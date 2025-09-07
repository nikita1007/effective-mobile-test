<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Hash;
use App\Traits\JwtTrait;
use App\Models\User;

class UserService {
    use JwtTrait;

    /**
     * Аутентифицировать пользователя по логину и паролю.
     *
     * @param string $login
     * @param string $password
     * @return array|null
     * @throws Exception
     */
    public function authenticate(string $login, string $password): ?array
    {
        $user = User::where('login', $login)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw new Exception('Неверный логин или пароль.', 401);
        }

        // Создаем токен для API
        $token = $this->generate_api_token($user->id);

        return [
            'user' => $user,
            'token' => $token
        ];
    }


    public function register(string $name, string $login, string $password): ?array {
        if (User::where('login', $login)->exists()) {
            throw new Exception('Пользователь с таким логином уже существует.', 409);
        }

        $user = User::create([
            'name' => $name,
            'login' => $login,
            'password' => Hash::make($password)
        ]);

        // Создаем токен для API
        $token = $this->generate_api_token($user->id);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Создание токена для API
     *
     * @param int $user_id
     * @return string
     */
    private function generate_api_token(int $user_id): string
    {
        return $this->jwt_generate(['user_id' => $user_id]);
    }
}

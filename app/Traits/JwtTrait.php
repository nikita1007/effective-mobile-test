<?php

namespace App\Traits;

use Firebase\JWT\{JWT, Key};
use stdClass;

trait JwtTrait
{
    protected function jwt_generate(array $params): string {
        $secret = config('app.jwt.key');
        $jwt = JWT::encode($params, $secret, 'HS256');
        return $jwt;
    }

    protected function jwt_decode(string $token): array {
        $secret = config('app.jwt.key');
        $data = JWT::decode($token, new Key($secret, 'HS256'));
        return (array) $data;
    }
}

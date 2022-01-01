<?php


namespace App\Repository\Auth;

use App\Models\User;
use App\Repository\ApiAuthRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApiAuthRepository extends BaseRepository
    implements ApiAuthRepositoryInterface
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function generateToken(
        string $email,
        string $password,
        string $deviceName
    ): User {
        $user = $this->user->where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages(
                ['credentials' => __('auth.failed')]);
        }

        $token = $this->getTokenDetails($user->id);
        $expireTime = config('sanctum.expiration');

        if (!is_null($expireTime) && $token) {
            if (!$this->checkTokenExpired($expireTime, $token->created_at)) {
                echo $expireTime;
                return $this->prepareResponse($user,$token->token);
            }else {
                $this->revokeToken($user, $token->id);
            }
        }
        $token = $user->createToken($deviceName)->plainTextToken;
        return $this->prepareResponse($user, $token);
    }

    public function revokeTokens(User $user)
    {
        abort_unless(Auth::check(), 401, 'Unauthenticated');

        $user->tokens()->delete();
    }

    public function revokeToken(User $user, int $tokenId)
    {
        $user->tokens()->where('id', $tokenId )->delete();
    }

    public function getTokenDetails(int $userId)
    {
        return DB::table('personal_access_tokens as t')
            ->select( 't.created_at', 'tokenable_id', 't.id')
            ->join('users as u', 't.tokenable_id', '=', 'u.id')
            ->where('u.id', $userId)
            ->orderBy('t.id', 'desc')
            ->first();
    }

    public function checkTokenExpired(int $expireTime, string $createdDate)
    {
        return now()->greaterThan(Carbon::parse($createdDate)
            ->addMinutes($expireTime));
    }

    public function prepareResponse(User $user, string $token)
    {
               $user['token'] = $token;
               return $user;
    }


}
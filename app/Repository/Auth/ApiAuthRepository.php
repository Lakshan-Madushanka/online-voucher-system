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

        if ($token) {
            $this->revokeToken($user, $token->id);
        }
        $token = $user->createToken($deviceName)->plainTextToken;

        $this->insertUserDeviceInformation($token);

        return $this->prepareResponse($user, $token, $expireTime);
    }

    public function revokeTokens(User $user)
    {
        $user->tokens()->delete();
    }

    public function revokeToken(User $user, int $tokenId)
    {
        $user->tokens()->where([
            ['id', $tokenId],
            ['ip_address', request()->ip()],
            ['browser', request()->userAgent()],
        ])->delete();
    }

    public function getTokenDetails(int $userId)
    {
        return DB::table('personal_access_tokens as t')
            ->select('t.created_at', 'tokenable_id', 't.id')
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

    public function prepareResponse(
        User $user,
        string $token,
        ?int $expiresIn = null
    ) {
        $expireTimestamp = null;
        if ($expiresIn) {
            $expireTimestamp = now()->addMinutes($expiresIn)->valueOf();
        }
        $user['token'] = $token;
        $user['expires_in'] = $expireTimestamp;

        return $user;
    }

    public function insertUserDeviceInformation(string $token)
    {
        $tokenID = explode("|", $token)[0];
        DB::table('personal_access_tokens')
            ->where('id', (int) $token)
            ->update([
                'ip_address' => request()->ip(),
                'browser'    => request()->userAgent(),
            ]);
    }

    public function logout(User $user)
    {
        $tokenId = $this->getTokenDetails($user->id)->id;
        $this->revokeToken($user, $tokenId);
    }

}
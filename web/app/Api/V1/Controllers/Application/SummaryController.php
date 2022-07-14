<?php

namespace App\Api\V1\Controllers\Application;

use App\Api\V1\Controllers\Controller;
use App\Models\ReferralCode;
use App\Models\Total;
use App\Models\User;
use Illuminate\Http\Request;
use Throwable;

/**
 * Referral code Controller
 *
 * @package App\Api\V1\Controllers\Application
 */
class SummaryController extends Controller
{
    /**
     * Get referral and codes summary
     *
     * @OA\Get(
     *     path="/summary",
     *     description="Get referral programm summary",
     *     tags={"Summary"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *
     *     @OA\Response(
     *         response="200",
     *         description="TOP 1000 of leaders in the invitation referrals",
     *
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="totalReferrals",
     *                     type="integer",
     *                     description="Total referrals by user",
     *                     example=10,
     *                 ),
     *                 @OA\Property(
     *                     property="totalCodesGenerated",
     *                     type="integer",
     *                     description="Total codes generated by user",
     *                     example=10,
     *                 ),
     *                 @OA\Property(
     *                     property="topReferralBonus",
     *                     type="integer",
     *                     description="Top referral bonus",
     *                     example=645000,
     *                 ),
     *                 @OA\Property(
     *                      property="amountEarned",
     *                      type="integer",
     *                      description="Amount earned by user",
     *                      example=450000,
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="User not found",
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     )
     * )
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        try {

            $referrerId = Auth()->user()->getAuthIdentifier();

            $referrers = User::whereNotNull('referrer_id')->distinct('referrer_id')->select('referrer_id')->get();

            $retVal = $referrers->map(function ($referrer) {
                $user = User::query()->where('id', $referrer->referrer_id)->first();
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'country' => $user->country,
                    'totalReferrals' => User::query()->where('referrer_id', $referrer->referrer_id)->count(),
                    'totalCodesGenerated' => ReferralCode::query()->where('user_id', $referrer->referrer_id)->count(),
                    'amountEarned' => Total::query()->where('user_id', $referrer->referrer_id)->sum('reward'),
                    'topReferralBonus' => Total::query()->max('reward'),
                    'rank' => 0,
                ];
            });

            $summary = collect($retVal)->sortByDesc('amountEarned')
                ->values()
                ->map(function ($item, $key) {
                    return [
                        'id' => $item['id'],
                        'name' => $item['name'],
                        'country' => $item['country'],
                        'totalReferrals' => $item['totalReferrals'],
                        'totalCodesGenerated' => $item['totalCodesGenerated'],
                        'amountEarned' => $item['amountEarned'],
                        'topReferralBonus' => $item['topReferralBonus'],
                        'rank' => $key + 1,
                    ];
                })->filter(function ($item) use ($referrerId) {
                    return $item['id'] == $referrerId;
                });


            return response()->jsonApi([
                'type' => 'success',
                'title' => "List referral and codes summary",
                'message' => 'Referral and codes summary successfully received',
                'data' => $summary,
            ], 200);
        } catch (Throwable $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Not received list",
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * @return mixed
     */
    protected function getTopReferralBonus(): mixed
    {
        $users = Total::distinct('user_id')->get('user_id');
        $topReferralBonus = $users->map(function ($user) {
            $userId = $user->user_id;
            $total = Total::where('user_id', $userId)->sum('reward');
            return [
                'total' => $total,
            ];
        })->sortByDesc('total')->first();
        return $topReferralBonus['total'];
    }

}

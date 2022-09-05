<?php

namespace App\Api\V1\Controllers\Admin;

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
     * Get referral and codes summary listing
     *
     * @OA\Get(
     *     path="/summary-listing",
     *     description="Get referral programm summary listing",
     *     tags={"Admin | Summary"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
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
     *                     property="name",
     *                     type="string",
     *                     description="user name",
     *                     example=10,
     *                 ),
     *                 @OA\Property(
     *                     property="country",
     *                     type="string",
     *                     description="user country",
     *                     example=10,
     *                 ),
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
     *                 @OA\Property(
     *                      property="rank",
     *                      type="integer",
     *                      description="Rank of user",
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
     *         response=400,
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
    public function listing(Request $request): mixed
    {
        try {

            $referrers = User::whereNotNull('referrer_id')->distinct('referrer_id')->select('referrer_id')->get();

            $retVal = $referrers->map(function ($referrer) {
                $user = User::query()->where('id', $referrer->referrer_id)->first();

                return [
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
                ->values()->map(function ($item, $key) {
                    return [
                        'name' => $item['name'],
                        'country' => $item['country'],
                        'totalReferrals' => $item['totalReferrals'],
                        'totalCodesGenerated' => $item['totalCodesGenerated'],
                        'amountEarned' => $item['amountEarned'],
                        'topReferralBonus' => $item['topReferralBonus'],
                        'rank' => $key + 1,
                    ];
                });

            $summary = collect($summary)->paginate(request()->get('limit', config('settings.pagination_limit')));

            return response()->jsonApi([
                'title' => "List referral and codes summary",
                'message' => 'Referral and codes summary successfully received',
                'data' => $summary,
            ]);
        } catch (Throwable $e) {
            return response()->jsonApi([
                'title' => "Not received list",
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}

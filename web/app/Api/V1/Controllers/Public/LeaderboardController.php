<?php

namespace App\Api\V1\Controllers\Public;

use App\Api\V1\Controllers\Controller;
use App\Models\User;
use App\Traits\LeaderboardTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Throwable;

class LeaderboardController extends Controller
{
    use LeaderboardTrait;

    /**
     *  A list of leaders in the invitation referrals
     *
     * @OA\Get(
     *     path="/subscribers/leaderboard",
     *     description="A list of leaders in the invitation referrals",
     *     tags={"Public Leaderboard"},
     *
     *     security={{
     *         "default" :{
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Limit leaderboard of page",
     *         @OA\Schema(
     *             type="number"
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Count leaderboard of page",
     *         @OA\Schema(
     *             type="number"
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="graph_filtr",
     *         in="query",
     *         description="Sort option for the graph. Possible values: week, month, year",
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="TOP 1000 of leaders in the invitation referrals",
     *
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="string",
     *                     description="user uuid",
     *                     example="fd069ebe-cdea-3fec-b1e2-ca5a73c661fc",
     *                 ),
     *                 @OA\Property(
     *                     property="user_id",
     *                     type="string",
     *                     description="user id",
     *                     example="edd72fdb-c83c-3e27-9047-d840e8745c61",
     *                 ),
     *                 @OA\Property(
     *                     property="username",
     *                     type="string",
     *                     description="username",
     *                     example="Lonzo",
     *                 ),
     *                 @OA\Property(
     *                      property="amount",
     *                      type="integer",
     *                      description="Number of invited users",
     *                      example=100,
     *                 ),
     *                 @OA\Property(
     *                      property="reward",
     *                      type="double",
     *                      description="Amount of remuneration",
     *                      example=50.50,
     *                 ),
     *                 @OA\Property(
     *                      property="is_current",
     *                      type="boolean",
     *                      description="Determine the user who made the request",
     *                 ),
     *             ),
     *             @OA\Property(
     *                 property="informer",
     *                 type="object",
     *                 @OA\Property(
     *                      property="rank",
     *                      type="integer",
     *                      description="User rating place",
     *                      example=1000000000,
     *                 ),
     *                 @OA\Property(
     *                      property="reward",
     *                      type="integer",
     *                      description="How much user earned",
     *                      example=7,
     *                 ),
     *                 @OA\Property(
     *                      property="growth_this_month",
     *                      type="integer",
     *                      description="",
     *                      example=100000,
     *                 ),
     *             ),
     *             @OA\Property(
     *                 property="leaderboard",
     *                 type="object",
     *                 @OA\Property(
     *                      property="rank",
     *                      type="integer",
     *                      description="User ranking place",
     *                      example=1,
     *                 ),
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                      description="User name",
     *                      example="Vsaya",
     *                 ),
     *                  @OA\Property(
     *                      property="channels",
     *                      type="string",
     *                      description="Platform used for referral",
     *                      example="WhatsApp",
     *                 ),
     *                 @OA\Property(
     *                      property="invitees",
     *                      type="integer",
     *                      description="Number of invited users",
     *                      example=10,
     *                 ),
     *                 @OA\Property(
     *                      property="Country",
     *                      type="string",
     *                      description="User country",
     *                      example="Ukraine",
     *                 ),
     *                 @OA\Property(
     *                      property="growth_this_month",
     *                      type="integer",
     *                      description="",
     *                      example=100000,
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="User not found",
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     ),
     * )
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        try {
            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Retrieval success',
                'message' => 'Leaderboard successfully generated',
                'data' => $this->leaderboard($request),

            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Not operation",
                'message' => "Error showing all users",
                'data' => null,
            ], 404);
        } catch (Throwable $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Not operation",
                'message' => $e->getMessage(),
                'data' => null,
            ], 404);
        }
    }


    /**
     * @param string $country
     * @param string|null $city
     *
     * @return array
     */
    protected function getUserIDByCountryCity(string $country, string $city = null): array
    {
        if ($country and $city != null) {
            //TODO get user id by country and city from identity ms
            return [];
        }
        //TODO get user id by country from identity ms
        return User::whereCountry($country)->get();
    }


}
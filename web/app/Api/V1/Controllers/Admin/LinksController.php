<?php

namespace App\Api\V1\Controllers\Admin;

use App\Helpers\AdminListing;
use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Traits\AdminUserCheckTrait;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;

/**
 * Class LinksController
 *
 * @package App\Http\Controllers
 */
class LinksController extends Controller
{
    use AdminUserCheckTrait;

    /**
     * Method for get list all referral links of users
     *
     * @OA\Get(
     *     path="/v1/referrals/admin/links",
     *     description="Get referral links",
     *     tags={"Admin"},
     *
     *     @OA\Parameter(
     *         name="user-id",
     *         description="User ID",
     *         in="header",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="orderBy",
     *         description="Order By",
     *         required=false,
     *         in="query",
     *         @OA\Schema (
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="orderDirection",
     *         description="Order Direction",
     *         required=false,
     *         in="query",
     *         @OA\Schema (
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         description="Search keywords",
     *         required=false,
     *         in="query",
     *         @OA\Schema (
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         description="Number of page",
     *         required=false,
     *         in="query",
     *         @OA\Schema (
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         description="Items per page",
     *         required=false,
     *         in="query",
     *         @OA\Schema (
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="bulk",
     *         description="Bulk filter",
     *         required=false,
     *         in="query",
     *         @OA\Schema (
     *             type="boolean"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     * @throws \Exception
     */
    public function index(Request $request)
    {
        // admin check
        if (($response = $this->adminUserCheck($request)) !== true) {
            return $response;
        }

        // Return json items list for ajax
        $validator = Validator::make($request->all(), [
            'orderBy' => 'in:id,user_id,package_name,referral_link|nullable',
            'orderDirection' => 'in:asc,desc|nullable',
            'search' => 'string|nullable',
            'page' => 'integer|nullable',
            'per_page' => 'integer|nullable'
        ]);

        if ($validator->fails()) {
            return response()->jsonApi([
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // create AdminListing instance for a specific model
        $data = AdminListing::create(Link::class)->processRequestAndGet(
            $request,

            // set columns to query
            [
                'id',
                'user_id',
                'package_name',
                'referral_link'
            ],

            // set columns to searchIn
            [
                'id',
                'package_name',
                'referral_link'
            ]
        );

        // Return bulk items
        if ($request->has('bulk')) {
            $data = ['bulkItems' => $data->pluck('id')];
        }

        // Return json items list by ajax
        return response()->jsonApi(json_decode($data->toJson()), 200);
    }
}

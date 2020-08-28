<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TriposoController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/triposo/articles",
     *     tags={"Triposo"},
     *     summary="Find triposo articles by city",
     *     description="Find triposo articles by city.",
     *     operationId="triposo_articles",
     *     deprecated=true,
     *     @OA\Parameter(
     *         name="city",
     *         in="query",
     *         description="required | string",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error"
     *     )
     * )
     */
    function articles(Request $request)
    {
        $location_ids = str_replace(' ', '_', ucwords(strtolower($request->city)));
        $account = env('X_TRIPOSO_ACCOUNT');
        $token = env('X_TRIPOSO_TOKEN');

        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET',
            "https://www.triposo.com/api/20200803/article.json?location_ids=$location_ids&account=$account&token=$token"
        );

        // echo $response;
        return response($response->getBody(), $response->getStatusCode());
        // return $response->json(); // $response->getBody();

        // return response()->json($response->getBody(), $response->getStatusCode());
    }
}

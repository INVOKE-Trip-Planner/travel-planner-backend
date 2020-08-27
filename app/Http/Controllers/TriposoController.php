<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TriposoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/triposo/articles",
     *     tags={"Triposo"},
     *     summary="Find triposo articles by city",
     *     description="Find triposo articles by city",
     *     operationId="triposo_articles",
     *     deprecated=false,
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
        $location_ids = $request->city;
        $account = '2XZIH948'; //'X-Triposo-Account' => '2XZIH948',
        $token = '61pspj7u7euzhk6b79q687nbycfxextm'; // 'X-Triposo-Token' => '61pspj7u7euzhk6b79q687nbycfxextm',

        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET',
            "https://www.triposo.com/api/20200803/article.json?location_ids=$location_ids&account=$account&token=$token"
        );

        // echo $response;
        return $response->getBody();

        // return response()->json($response->getBody(), $response->getStatusCode());
    }
}

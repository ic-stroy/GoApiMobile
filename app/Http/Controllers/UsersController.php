<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users/show",
     *     tags={"Users"},
     *     summary="Finds Pets by status",
     *     description="Multiple status values can be provided with comma separated string",
     *     operationId="show",
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Status values that needed to be considered for filter",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             default="available",
     *             type="string",
     *             enum={"available", "pending", "sold"},
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid status value"
     *     ),
     *     security={
     *         {"bearer_token": {}}
     *     }
     * )
     */
    public function show(){

        $model = Auth::user();
        $first_name = $model->personalInfo->first_name?$model->personalInfo->first_name.' ':[''];
        $last_name = $model->personalInfo->last_name?$model->personalInfo->last_name.'. ':[''];
        $middle_name = $model->personalInfo->middle_name?$model->personalInfo->middle_name.'.':[''];
        $list = [
          'img'=>$model->personalInfo->avatar,
          'full_name'=>$first_name.''.strtoupper($last_name[0]).''.strtoupper($middle_name[0]),
          'birth_date'=>$model->personalInfo->birth_date,
          'gender'=>$model->personalInfo->gender,
          'phone_number'=>$model->personalInfo->phone_number,
//          'rating'=>$model->personalInfo,
        ];
        $response = [
            'status'=>true,
            'message'=>'success',
            'list'=>$list
        ];
        return response()->json($response);
    }
    /**
     * @OA\Post(
     *     path="/api/users/update",
     *     tags={"Users"},
     *     summary="Update user",
     *     operationId="update",
     *     @OA\Response(
     *         response=405,
     *         description="Invalid input"
     *     ),
     *     @OA\RequestBody(
     *         description="Input data format",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="first_name",
     *                     description="write your firstname",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="last_name",
     *                     description="write your lastname",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="middle_name",
     *                     description="write your middlename",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="birth_date",
     *                     description="write your birth date format data(1999-01-21)" ,
     *                     type="date",
     *                 ),
     *                 @OA\Property(
     *                     property="gender",
     *                     description="write your gender",
     *                     type="integer",
     *                 ),
     *                 @OA\Property(
     *                     property="phone_number",
     *                     description="write your phone number",
     *                     type="string",
     *                 ),
     *             )
     *         )
     *     ),
     *     security={
     *         {"bearer_token": {}}
     *     }
     * )
     */
    public function update(Request $request){
        $model = Auth::user();
        $personal_info = $model->personalInfo;
        $personal_info->first_name = $request->first_name;
        $personal_info->last_name = $request->last_name;
        $personal_info->middle_name = $request->middle_name;
        $personal_info->birth_date = $request->birth_date;
        $personal_info->gender = $request->gender;
        $personal_info->phone_number = $request->phone_number;
        $personal_info->save();
        $response = [
            'status'=>true,
            'message'=>'success'
        ];
        return response()->json($response);
    }
    /**
     * @OA\Post(
     *     path="/api/users/delete",
     *     tags={"Users"},
     *     summary="Delete user",
     *     operationId="delete",
     *     @OA\Response(
     *         response=405,
     *         description="Invalid input"
     *     ),
     *     @OA\RequestBody(
     *         description="Input data format",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object"
     *             )
     *         )
     *     ),
     *     security={
     *         {"bearer_token": {}}
     *     }
     * )
     */
    public function delete(){
        $model = Auth::user();
        $model->personalInfo->delete();
        $model->delete();
        $response = [
            'status'=>true,
            'message'=>'success'
        ];
        return response()->json($response);
    }
}
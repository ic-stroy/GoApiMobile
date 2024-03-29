<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserVerify;
use App\Models\PersonalInfo;
use App\Models\User;
use App\Models\Driver;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Offer;
use App\Models\Cars;
use App\Models\BalanceHistory;
use App\Models\Chat;
use App\Models\CommentScore;
use App\Models\Complain;
use App\Constants;
use Image;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
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
    public function show(Request $request){
        $language = $request->header('language');
        $model = Auth::user();
        if(isset($model->device_type) && isset($model->device_id)){
            $device_types = json_decode($model->device_type);
            $device_id = json_decode($model->device_id);
            $i = -1;
            foreach ($device_types as $device_type){
                $i++;
                $device[] = ['type'=>$device_type??null, 'id'=>$device_id[$i]??null];
            }
        }
        if(isset($model->personalInfo)){
            $first_name = $model->personalInfo->first_name?$model->personalInfo->first_name.' ':'';
            $last_name = $model->personalInfo->last_name?strtoupper($model->personalInfo->last_name[0].'. '):'';
            $middle_name = $model->personalInfo->middle_name?strtoupper($model->personalInfo->middle_name[0].'.'):'';
            if($first_name.''.strtoupper($last_name).''.strtoupper($middle_name) != ''){
                $full_name = $first_name.''.strtoupper($last_name).''.strtoupper($middle_name);
            }else{
                $full_name = null;
            }
            if(isset($model->personalInfo->avatar)){
                $avatar = storage_path('app/public/avatar/'.$model->personalInfo->avatar);
                if(file_exists($avatar)){
                    $model->personalInfo->avatar = asset('storage/avatar/'.$model->personalInfo->avatar);
                }
            }
            if(isset($model->driver)){
                if(isset($model->driver->doc_status)){
                    $doc_status = $model->driver->doc_status;
                }
                if(isset($model->driver->license_number)){
                    $license_number = $model->driver->license_number;
                }
                if(isset($model->driver->license_expired_date)){
                    $license_expired_date = explode(" ", $model->driver->license_expired_date);
                }
                if(isset($model->driver->cars)){
                    foreach ($model->driver->cars as $car){
                        if(isset($car->reg_certificate)){
                            $reg_certificate[] = $car->reg_certificate;
                        }
                    }
                }
            }
            $driver = Driver::where('user_id', $request->user_id)->first();
            
            $list = [
                'id'=>$model->id,
                'device'=>$device??[],
                'img'=>$model->personalInfo->avatar??null,
                'first_name'=>$model->personalInfo->first_name??null,
                'last_name'=>$model->personalInfo->last_name??null,
                'full_name'=>$full_name,
                'birth_date'=>$model->personalInfo->birth_date??null,
                'doc_status'=>(int)($doc_status??1),
                'licence_number'=>$license_number??null,
                'license_expired_date'=>$license_expired_date[0]??null,
                'reg_certificate'=>$reg_certificate??[],
                'email'=>$model->personalInfo->email??null,
                'gender'=>$model->personalInfo->gender??null,
                'phone_number' => $model->personalInfo->phone_number ? substr($model->personalInfo->phone_number, 3) : null,
                'rating'=>$model->rating??null,
            ];
            return $this->success('Success', 201, $list);
        }else{
            return $this->error(translate_api('No personal info', $language), 201, $device??null);
        }
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
     *                     property="email",
     *                     description="write your email",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="avatar",
     *                     description="Enter your photo",
     *                     type="file",
     *                 ),
     *             )
     *         )
     *     ),
     *     security={
     *         {"bearer_token": {}}
     *     }
     * )
     */
    public function update(Request $request)
    {
        $language = $request->header('language');
        $model = Auth::user();
        if(isset($model->personalInfo->id)){
            $personal_info = $model->personalInfo;
        }else{
            $personal_info = new PersonalInfo();
        }
        if(isset($request->first_name)){
            $personal_info->first_name = $request->first_name;
        }
        if(isset($request->last_name)){
            $personal_info->last_name = $request->last_name;
        }
        if(isset($request->middle_name)){
            $personal_info->middle_name = $request->middle_name;
        }
        if(isset($request->birth_date)){
            $personal_info->birth_date = $request->birth_date;
        }
        if(isset($request->gender)){
            $personal_info->gender = $request->gender;
        }
        if(isset($request->email)){
            $personal_info->email = $request->email;
        }
        $letters = range('a', 'z');
        $user_random_array = [$letters[rand(0,25)], $letters[rand(0,25)], $letters[rand(0,25)], $letters[rand(0,25)], $letters[rand(0,25)]];
        $user_random = implode("", $user_random_array);
        $user_img = $request->file('avatar');
        if(isset($user_img)){
            if(isset($personal_info->avatar) && $personal_info->avatar != ''){
                $avatar = storage_path('app/public/avatar/'.$personal_info->avatar??'no');
                if(file_exists($avatar)){
                    unlink($avatar);
                }
            }
            $file_size = round($user_img->getSize()/1024);
            if($file_size>50000){
                $x = 0.2;
            }
            elseif($file_size>20000){
                $x = 0.5;
            }
            elseif($file_size>10000){
                $x = 1;
            }elseif($file_size>5000){
                $x = 2;
            }elseif($file_size>1000){
                $x = 10;
            }elseif($file_size>500){
                $x = 20;
            }elseif($file_size>250){
                $x = 50;
            }elseif($file_size>125){
                $x = 100;
            }elseif($file_size>75){
                $x = 100;
            }else{
                $x = 100;
            }
//            dd($file_size, $x);
            $image_name = $user_random . '' . date('Y-m-dh-i-s') . '.' . $user_img->extension();
            $img = Image::make($user_img->path());
            $img->save(storage_path('app/public/avatar/'.$image_name), $x);
            $personal_info->avatar = $image_name;
        }
        $personal_info->save();
        return $this->success(translate_api('Success', $language), 201);
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
    public function delete(Request $request)
    {
        date_default_timezone_set("Asia/Tashkent");
    
        $model = Auth::user();
        if (isset($model->personalInfo)) {
            $model->personalInfo->deleted_at = date("Y-m-d H:i:s");
            $model->personalInfo->save();
        }

        $driver = Driver::where('user_id', $model->id)->first();
        if (isset($driver->id)) {
            $driver_id = $driver->id;
            $driver->deleted_at = date("Y-m-d H:i:s");
            $driver->save();
        }

        $user_verify = UserVerify::where('user_id', $model->id)->first();
        if (isset($user_verify->id)) {
            $user_verify->deleted_at = date("Y-m-d H:i:s");
            $user_verify->save();
        }

        // New logics
        if ($model->id) {
            $orders = Order::where('driver_id', $model->id)->get();
            $arrOrderIds = [];
            if (isset($orders) && count($orders) > 0) {
                foreach ($orders as $order) {
                    $arrOrderIds[] = $order->id;
                    $order->deleted_at = date("Y-m-d H:i:s");
                    $order->save();
                }
            }

            $orderDetails = OrderDetail::where('client_id', $model->id)->get();
            $arrOrderDetailIds = [];
            if (isset($orderDetails) && count($orderDetails) > 0) {
                foreach ($orderDetails as $orderDetail) {
                    $arrOrderDetailIds[] = $orderDetail->id;
                    $orderDetail->deleted_at = date("Y-m-d H:i:s");
                    $orderDetail->save();
                }
            }


            $balanceHistories = BalanceHistory::where('user_id', $model->id)->get();
            if (isset($balanceHistories) && count($balanceHistories) > 0) {
                foreach ($balanceHistories as $balanceHistory) {
                    $balanceHistory->deleted_at = date("Y-m-d H:i:s");
                    $balanceHistory->save();
                }
            }
        }

        if (!empty($arrOrderIds) || !empty($arrOrderDetailIds)) {
            $offers = Offer::whereIn('order_id', $arrOrderIds)->orWhereIn('order_detail_id', $arrOrderDetailIds)->get();
            if (isset($offers) && count($offers) > 0) {
                foreach ($offers as $offer) {
                    $offer->deleted_at = date("Y-m-d H:i:s");
                    $offer->save();
                }
            }

            $chats = Chat::whereIn('order_id', $arrOrderIds)->orWhereIn('order_detail_id', $arrOrderDetailIds)->get();
            if (isset($chats) && count($chats) > 0) {
                foreach ($chats as $chat) {
                    $chat->deleted_at = date("Y-m-d H:i:s");
                    $chat->save();
                }
            }
            
            $complains = Complain::whereIn('order_id', $arrOrderIds)->get();
            if (isset($complains) && count($complains) > 0) {
                foreach ($complains as $complain) {
                    $complain->deleted_at = date("Y-m-d H:i:s");
                    $complain->save();
                }
            }
        }

        if ($driver_id) {
            $cars = Cars::where('driver_id', $driver_id)->get();
            if (isset($cars) && count($cars) > 0) {
                foreach ($cars as $car) {
                    $car->deleted_at = date("Y-m-d H:i:s");
                    $car->save();
                }
            }
            
            $commentScores = CommentScore::whereIn('order_id', $arrOrderIds)->orWhere('driver_id', $driver_id)->get();
            if (isset($commentScores) && count($commentScores) > 0) {
                foreach ($commentScores as $commentScore) {
                    $commentScore->deleted_at = date("Y-m-d H:i:s");
                    $commentScore->save();
                }
            }
        }

        $model->deleted_at = date("Y-m-d H:i:s");
        $model->save();

        return $this->success('Success', 201);
    }
    
    public function getUser(Request $request){
        $language = $request->header('language');
        $user = User::withTrashed()->where('id', $request->id)->first();
        if(isset($user->id) && !isset($user->deleted_at)) {
            return response()->json([
                'users' => $user,
                'sms_token' => isset($user->userVerify) ? $user->userVerify->verify_code : null
            ]);
        }else{
            return response()->json([
                'status' => 'deleted',
                'users' => $user,
                'sms_token' => isset($user->userVerify) ? $user->userVerify->verify_code : null
            ]);
        }
    }

    public function setLanguage(Request $request)
    {
        $user = Auth::user();
        if(!isset($request->language)){
            return $this->error('Send language', 400);
        }
        $user->language = $request->language;
        $user->save();
        return $this->success('Success', 201);
    }

    public function setFirebaseToken(Request $request)
    {
        $user = Auth::user();
        $language = $request->header('language');

        if (!isset($request->device_id)) {
            return $this->error(translate_api('device_id not found', $language), 200);
        }
        
        $deviceId = $user->device_id ? json_decode($user->device_id) : [];
        if (in_array($request->device_id, $deviceId)) {
            return $this->error(translate_api('This device_id was previously registered', $language), 200);
        }

        $deviceId[] = $request->device_id;

        $user->device_id = $deviceId;
        $user->save();
        
        return $this->success(translate_api('Success', $language), 200);
    }
    
    public function getId() 
    {
        $model = Auth::user();

        if(!$model)
            return $this->error('A token error occurred', 400);

        return $this->success('success', 200, ['id' => $model->id]);
    }

    public function isDriverAccept(Request $request)
    {
        $language = $request->header('language');
        
        $model = Auth::user();

        $arr = [];
        $doc_status = Constants::NOT_ACCEPTED_USER;
        if ($model->doc_status == Constants::WAITING_ACCEPTING_USER) {
            $doc_status = Constants::WAITING_ACCEPTING_USER;
        } else if ($model->doc_status == Constants::ACCEPTED_USER || $model->doc_status == Constants::ACCEPTED_USER_FIRST) {
            $doc_status = Constants::ACCEPTED_USER;

            $avatar = '';
            if (isset($model->personalInfo->avatar)) {
                $avatar = storage_path('app/public/avatar/' . $model->personalInfo->avatar);
            }

            $usersCar = Cars::where('driver_id', $model->id)->where('type', Constants::ACCEPTED_CAR)->first();
            // return $usersCar;

            $carName = '';
            $carNumber = '';
            $reg_certificate = '';
            if (isset($usersCar)) {
                $reg_certificate = $usersCar->reg_certificate_number;
                $carNumber = $usersCar->reg_certificate;
                if (isset($usersCar->carList) && isset($usersCar->carList->type)) {
                    $carName = $usersCar->carList->type->name . ' ' . $usersCar->carList->name;
                }
            }

            if ($model->personalInfo) {
                $arr = [
                    "last_name" => $model->personalInfo->last_name,
                    "first_name" => $model->personalInfo->first_name,
                    "middle_name" => $model->personalInfo->middle_name,
                    "fill_name" => $model->personalInfo->last_name . ' ' . $model->personalInfo->first_name . ' ' . $model->personalInfo->middle_name,
                    "avatar" => $avatar,
                    "phone_number" => $model->personalInfo->phone_number,
                    "license_number" => ($model->driver) ? $model->driver->license_number : '',
                    "license_expired_date" => ($model->driver) ? $model->driver->license_expired_date : '',
                    "car_name" => $carName,
                    "car_number" => $carNumber,
                    "reg_certificate" => $reg_certificate
                ];
            }
        }

        $is_first = $model->doc_status;
        if ($model->doc_status == Constants::ACCEPTED_USER_FIRST) {
            $model->doc_status = Constants::ACCEPTED_USER;
            $model->save();
        }

        return $this->success(translate_api('success', $language), 200, [
            'doc_status' => $doc_status,
            'is_first' => (($is_first == Constants::ACCEPTED_USER_FIRST) ? true : false),
            'driver_info' => (isset($arr) && !empty($arr)) ? $arr : NULL,
        ]);

        // return response()->json([
        //     'status' => true,
        //     'message' => translate_api('success', $language),
        //     'doc_status' => $doc_status,
        //     'is_first' => (($is_first == Constants::ACCEPTED_USER_FIRST) ? true : false),
        //     'data' => $arr ?? NULL,
        // ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
    }

    public function driverAccept(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'car_id' => 'required|integer',
            'license_number' => 'required|string',
            'license_expired_date' => 'required|date',
            // 'license_image' => 'required|string',
            // 'license_image_back' => 'required|string',
            // 'license_image_selfie' => 'required|string',
        
            'reg_certificate_number' => 'required|string',
            // 'reg_certificate_image' => 'required|string',
            // 'reg_certificate_image_back' => 'required|string',
        ]);

        if ($validator->fails())
            return $this->error($validator->errors()->first(), 400);

        $user = Auth::user();
        $language = $request->header('language');

        $modelDriver = Driver::where('user_id', $user->id)->first();

        if (isset($modelDriver))
            return $this->error(translate_api('The user_id you submitted has already been verified', $language), 400);

        $car = Cars::where('id', $request->car_id)->where('driver_id', $user->id)->first();

        if (!isset($car))
            return $this->error(translate_api('No information was found matching the car_id you submitted', $language), 400);

        $newDriver = new Driver();
        $newDriver->user_id = $user->id;
        $newDriver->license_number = $request->license_number;
        $newDriver->license_expired_date = $request->license_expired_date;
        // $newDriver->license_image = $request->license_image;
        // $newDriver->license_image_back = $request->license_image_back;
        // $newDriver->license_image_selfie = $request->license_image_selfie;
        $newDriver->from_admin = 0;
        $newDriver->save();

        $this->handleImageUpload($request, $newDriver, 'license_image', 'certificate');
        $this->handleImageUpload($request, $newDriver, 'license_image_back', 'certificate');
        $this->handleImageUpload($request, $newDriver, 'license_image_selfie', 'certificate');

        $car->reg_certificate_number = $request->reg_certificate_number;
        $car->type = Constants::ACCEPTED_CAR;
        // $car->reg_certificate_image = $request->reg_certificate_image;
        // $car->reg_certificate_image_back = $request->reg_certificate_image_back;
        $car->save();

        $this->handleImageUpload($request, $car, 'reg_certificate_image', 'cars');
        $this->handleImageUpload($request, $car, 'reg_certificate_image_back', 'cars');

        $user->doc_status = Constants::ACCEPTED_USER_FIRST;
        $user->type = 1;
        $user->save();

        return $this->success(translate_api('success', $language), 200);
    }

    private function handleImageUpload($request, $model, $imageName, $folderName)
    {
        $letters = range('a', 'z');
        if ($request->hasFile($imageName)) {
            $randomArray = [];
            for ($i = 0; $i < 5; $i++) {
                $randomArray[] = $letters[rand(0, 25)];
            }
            $randomString = implode("", $randomArray);

            $image = $request->file($imageName);
            $imageNameGen = $randomString . '' . now()->format('Y-m-dh-i-s') . '.' . $image->extension();
            $image->storeAs('public/' . $folderName . '/', $imageNameGen);

            $model->{$imageName} = $imageNameGen;
            $model->from_admin = 0;
            $model->save();
        }
    }
}

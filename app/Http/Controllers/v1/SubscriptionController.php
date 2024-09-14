<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Utilities\Request as UtilityRequest;
use App\Http\Utilities\Response;
use App\Models\Padcast;
use App\Models\PadcastCategory;
use App\Models\Subscription;
use App\Models\SubscriptionFeature;
use Illuminate\Http\Request;
use App\Http\Utilities\StatusCode;
use App\Models\LoginCode;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class SubscriptionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    use Response, StatusCode, UtilityRequest;

    public function __construct()
    {
    }

    public function addSubscription(Request $request)
    {
        DB::beginTransaction();
        try {
            $name = $request->name;
            $price = $request->price;
            $duration = $request->duration;
            $feature = $request->feature;

            $this->rules = [
                'name'=>'required|string',
                'price'=>'required|integer',
                'duration'=>'required',
                'feature'=>'required'];


            if (!$this->validateRequest($request->all())) {
                return $this->sendJsonResponse([], $this->validation_messages, $this->getStatusCodeByCodeName('Not Acceptable'));
            }


            $Subscription = new Subscription();
            $Subscription->name = $request->name;
            $Subscription->price = $request->price;
            $Subscription->duration = $request->duration;
            $Subscription->save();

            if($Subscription)
            {
                foreach ($feature as $fetur)
                {
                    $SubscriptionFeature=new SubscriptionFeature();
                    $SubscriptionFeature->feature=$fetur;
                    $SubscriptionFeature->subscription_id=$Subscription->id;
                    $SubscriptionFeature->save();
                }
            }

            DB::commit();
            return $this->sendJsonResponse($Subscription, trans('message.user_updated_successfully'), $this->getStatusCodeByCodeName('OK'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function editSubscription(Request $request,$id)
    {
        DB::beginTransaction();
        try {
            $name = $request->name;
            $price = $request->price;
            $duration = $request->duration;
            $feature = $request->feature;

            $this->rules = [
                'name' => 'required|string',
                'price' => 'required|integer',
                'duration' => 'required',
                'feature' => 'required|array',
            ];

            if (!$this->validateRequest($request->all())) {
                return $this->sendJsonResponse([], $this->validation_messages, $this->getStatusCodeByCodeName('Not Acceptable'));
            }

            // پیدا کردن اشتراک موجود
            $Subscription = Subscription::findOrFail($id);
            $Subscription->name = $name;
            $Subscription->price = $price;
            $Subscription->duration = $duration;
            $Subscription->save();

            // حذف ویژگی‌های قبلی
            SubscriptionFeature::where('subscription_id', $Subscription->id)->delete();

            // اضافه کردن ویژگی‌های جدید
            foreach ($feature as $fetur) {
                $SubscriptionFeature = new SubscriptionFeature();
                $SubscriptionFeature->feature = $fetur;
                $SubscriptionFeature->subscription_id = $Subscription->id;
                $SubscriptionFeature->save();
            }

            DB::commit();
            return $this->sendJsonResponse($Subscription, trans('message.user_updated_successfully'), $this->getStatusCodeByCodeName('OK'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function deleteSubscription(Request $request)
    {
        DB::beginTransaction();
        try {
            $subscription_id = $request->subscription_id;

            // Find the subscription by ID
            $Subscription = Subscription::find($subscription_id);
            if (!$Subscription) {
                return $this->sendJsonResponse([], trans('message.subscription_not_found'), $this->getStatusCodeByCodeName('Not Found'));
            }

            // Delete the subscription
            $Subscription->delete();

            DB::commit();
            return $this->sendJsonResponse([], trans('message.subscription_deleted_successfully'), $this->getStatusCodeByCodeName('OK'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function showSubscription(Request $request)
    {
        DB::beginTransaction();
        try {

            $Subscription=Subscription::with('SubscriptionFeauter')->get();

            DB::commit();
            return $this->sendJsonResponse($Subscription, trans('message.user_updated_successfully'), $this->getStatusCodeByCodeName('OK'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function showFeatureSubscription(Request $request){
        DB::beginTransaction();
        try {
            $subscription_id=$request->subscription_id;
            $SubscriptionFeature=SubscriptionFeature::where('subscription_id',$subscription_id)->get();

            DB::commit();
            return $this->sendJsonResponse($SubscriptionFeature, trans('message.user_updated_successfully'), $this->getStatusCodeByCodeName('OK'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

}

<?php

namespace Railroad\Railnotifications\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class NotificationRequest
 *
 * @package Railroad\Railnotifications\Requests
 *
 * @bodyParam type required  Notification type. Example: lesson comment reply
 * @bodyParam data required Example: array
 * @bodyParam recipient_id required Example: 1
 */
class NotificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function rules()
    {
        return [
            'type' => 'required',
            'data' => 'required',
            'recipient_id' => 'required',
        ];
    }
}
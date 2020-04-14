<?php

namespace Railroad\Railnotifications\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class BroadcastNotificationRequest
 *
 * @package Railroad\Railnotifications\Requests
 *
 * @bodyParam notification_id required  Notification id. Example: 1
 * @bodyParam channel  Example:
 */
class BroadcastNotificationRequest extends FormRequest
{
    /** * @bodyParam type required  Notification type. Example: Permission 1
 * @bodyParam data required Example:
 * @bodyParam recipient_id required
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
            'channel' => 'string|required',
            'notification_id' => 'required',
        ];
    }
}
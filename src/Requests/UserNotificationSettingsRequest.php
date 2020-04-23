<?php

namespace Railroad\Railnotifications\Requests;

/**
 * Class UserNotificationSettingsRequest
 *
 * @package Railroad\Railnotifications\Requests
 */
class UserNotificationSettingsRequest extends FormRequest
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
            'setting_name' => 'required',
            'setting_value' => 'required',
        ];
    }
}
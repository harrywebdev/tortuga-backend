<?php

namespace App\Http\Controllers;

use App\Order;
use App\Settings;
use Illuminate\Http\Request;
use Tortuga\ApiTransformer\GetOrderApiTransformer;
use Tortuga\ApiTransformer\GetOrdersApiTransformer;
use Tortuga\ApiTransformer\GetSettingsApiTransformer;
use Tortuga\AppSettings;
use Tortuga\Order\OrderCreationStrategy;
use Tortuga\Order\OrderStatus;
use Tortuga\SettingsName;
use Tortuga\Validation\InvalidDataException;
use Tortuga\Validation\JsonSchemaValidator;
use Tortuga\Validation\OrderSlotFullyBookedException;

class SettingsController extends Controller
{
    /**
     * @var JsonSchemaValidator
     */
    private $validator;

    /**
     * CustomerRegistrationStrategy constructor.
     * @param JsonSchemaValidator $validator
     */
    public function __construct(JsonSchemaValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        /** @var \Tortuga\AppSettings $settings */
        $settings = app()->make(\Tortuga\AppSettings::class);

        $transformer = new GetSettingsApiTransformer();

        return response()->json($transformer->output($settings->all()));
    }

    /**
     * @param Order   $order
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        try {
            $data = json_decode($request->getContent());
            $this->validator->validate(
                $data,
                'http://localhost/update_settings.json'
            );

            // currently, only `is_open_for_booking` is available to be changed by this request
            $key = SettingsName::IS_OPEN_FOR_BOOKING();

            $settings = Settings::where('name', '=', $key)->first();

            if ($data->data->attributes->{$key} !== (bool)$settings->value) {
                $settings->value = $data->data->attributes->{$key};
                $settings->save();
            }

            /** @var \Tortuga\AppSettings $settings */
            $settings = app()->make(\Tortuga\AppSettings::class);

            $transformer = new GetSettingsApiTransformer();

            return response()->json($transformer->output($settings->all()));
        } catch (InvalidDataException $e) {
            return $this->_returnError(422, 'JSON Schema Validation error', $e->getMessage(), $e->getDataPointer());
        }
    }
}

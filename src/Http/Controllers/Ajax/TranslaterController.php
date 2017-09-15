<?php

namespace curunoir\translation\Http\Controllers\Ajax;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Stichoza\GoogleTranslate\TranslateClient;
use curunoir\translation\Models\Locale;
use curunoir\translation\Facades\TranslationDyn as TransDynService;

class TranslaterController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @return mixed
     */
    public function postQuickUpdate()
    {
        $inputs = Input::all();
        $model = $name_model = $inputs['model'];
        if (!isset($inputs['exception'])) {
            $m = "App\\Models\\" . $model;
        } else {
            $m = "curunoir\\translation\\Models\\Locale";
        }

        if (isset($inputs['locale_id'])):
            $model = $m::where($inputs['column'], $inputs['pk'])
                ->where('locale_id', $inputs['locale_id'])
                ->first();
        elseif (isset($inputs['lang'])):
            $locale_id = Locale::where('code', session('code'))->first()->id;
            $params = [
                'model' => $name_model,
                'locale_id' => $locale_id,
                'field' => 'title',
                'object_id' => $inputs['pk'],
                'content' => $inputs['value']
            ];
            $model = TransDynService::addTrad($params);
            return $model;
        else:
            $model = $m::find($inputs['pk']);
        endif;

        if ($name_model == "Setting") {
            Cache::forget('settings');
        }

        $model->{$inputs['name']} = $inputs['value'];
        $model->save();
        return $model;
    }

    /**
     * Get a translation from google client
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceTrad(Request $request)
    {
        $req = $request->all();
        $locale = Locale::find($req['locale_id']);
        $localTarget = Locale::where('id', '!=', $req['locale_id'])->get();
        $gT = new TranslateClient();
        $gT->setSource($locale->code);
        $error = false;
        foreach ($localTarget as $l):
            $gT->setTarget($l->code);
            try {
                $textTrad[$l->id] = $gT->translate(mb_convert_encoding($req['content'], 'UTF-8', 'HTML-ENTITIES'));
            } catch (\ErrorException $e) {
                $error = true;
                // Request to translate failed, set the text
                // to the parent translation.

            } catch (\UnexpectedValueException $e) {
                $error = true;

                // Looks like something other than text was passed in,
                // we'll set the text to the parent translation
                // for this exception as well.
            }
        endforeach;

        return response()->json(['status' => !$error,'text' => $textTrad], 200);

    }
}

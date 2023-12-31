<?php

use App\Models\Translation;
use App\Models\Language;
use App\Models\Options;
use App\Models\ClassList;
use App\Models\ColorList;
// use Modules\ForTheBuilder\Entities\Language;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;



if (!function_exists('default_language')) {
    function default_language()
    {
        return env("DEFAULT_LANGUAGE", 'ru');
    }
}
if (!function_exists('translate_api')) {
    function translate_api($key, $lang = null)
    {
        
        if ($lang === null) {
            $lang = App::getLocale();
        }
        // dd($lang);

            $translate = Translation::where('lang_key', $key)
                ->where('lang', $lang)
                ->first();
                // dd($translate);
            if ($translate === null){
                // dd($translate);
                foreach (Language::all() as $language) {
                    if(!Translation::where('lang_key', $key)->where('lang', $language->code)->exists()){
                        Translation::create([
                            'lang'=>$language->code,
                            'lang_key'=> $key,
                            'lang_value'=>$key
                        ]);
                    }
                }
                // dd($translate);
                $data = $key;
            }else{
                $data = $translate->lang_value;
            }

            return $data;
        // };

        // return tkram(Translation::class, $app, $function);
    }
}


if (!function_exists('table_translate')) {
    function table_translate($key,$type, $lang)
    {   
        switch ($type) {
            case 'city':
                // dd($key);
                $from_name = DB::table('yy_cities as dt1')
                    ->leftJoin('yy_city_translations as dt2', 'dt2.city_id', '=', 'dt1.id')
                    ->where('dt1.id', $key->from_id)
                    // ->where('dt2.lang', $lang)
                    ->select('dt1.name as city_name', 'dt2.name as city_translation_name');
                    // ->first();

                // dd($from_name->first());
                // $name_from=$from_name->city_name;
                $name_from_txt = '';
                if ($from_name->where('dt2.lang', $lang)->first()) {
                    $from_name_where = $from_name->where('dt2.lang', $lang)->first();
                    $name_from_txt = $from_name_where->city_translation_name ?? $from_name_where->city_name;
                } else {
                    $name_from_txt = $from_name->first()->city_name ?? '';
                }

                $name_from = $name_from_txt;
                // dd($name_from);
                
                // dd($key->to_id);
                $to_name = DB::table('yy_cities as dt1')
                    ->leftJoin('yy_city_translations as dt2', 'dt2.city_id', '=', 'dt1.id')
                    ->where('dt1.id', $key->to_id)
                    // ->where('dt2.lang', $lang)
                    ->select('dt1.name as city_name', 'dt2.name as city_translation_name');
                    // ->first();
                // dd($to_name);
                // $name_to=$to_name->city_name;
                // dd($name_to);
                $name_to = '';
                if ($to_name->where('dt2.lang', $lang)->first()) {
                    $to_name_where = $to_name->where('dt2.lang', $lang)->first();
                    $name_to = $to_name_where->city_translation_name ?? $to_name_where->city_name;
                } else {
                    $name_to = $to_name->first()->city_name ?? '';
                }

                // $name_to = (isset($to_name)) ? (($to_name->city_translation_name) ? $to_name->city_translation_name : $to_name->city_name) : $key->to_name;
        
                $from_to=[
                    'from_name'=>$name_from,
                    'to_name'=>$name_to,
                ];
                // dd($from_to);
                return $from_to;
                break;

            case 'color':

                $color= DB::table('yy_color_lists as dt1')
                    ->leftJoin('yy_color_translations as dt2', function ($join) use ($lang) {
                        $join->on('dt2.color_list_id', '=', 'dt1.id')
                            ->where('dt2.lang', $lang);
                    })
                    ->where('dt1.id', $key->color_id)
                    ->select('dt1.name as color_name','dt1.code as color_code', 'dt2.name as color_translation_name')
                    ->first();


                if(!isset($color->color_translation_name) && isset($color->color_name)){
                    $color->color_translation_name = $color->color_name;
                }
                // $name_to=$from_name->city_name;
                $color_name = ($color->color_translation_name) ? $color->color_translation_name : $color->color_name;
                $color->color_name = $color_name;

                return $color;
                break;
//            case 'class_list':
//                $class_lists = ClassList::select('id', 'name')->get()->toArray();
//                foreach ($class_lists as $key => $class_list){
//                    $class_list_translate = DB::table('yy_class_lists as Class')
//                        ->leftJoin('yy_class_translations as ClassT', 'Class.id', '=', 'ClassT.class_list_id')
//                        ->where('Class.id', $class_list['id'])
//                        ->where('ClassT.lang', $lang)
//                        ->select('Class.id as id', 'ClassT.name as name')->first();
//                    $class_lists[$key]['name'] = $class_list_translate->name??$class_list['name'];
//                    $class_lists[$key]['id'] = $class_list['id'];
//                }
//                return $class_lists;
//                break;
            case 'color_list':
                $color_lists = ColorList::select('id', 'name')->get()->toArray();
                foreach ($color_lists as $key => $color_list){
                    $color_list_translate = DB::table('yy_color_lists as Color')
                        ->leftJoin('yy_color_translations as ColorT', 'Color.id', '=', 'ColorT.color_list_id')
                        ->where('Color.id', $color_list['id'])
                        ->where('ColorT.lang', $lang)
                        ->select('Color.id as id', 'ColorT.name as name')->first();
                    $color_lists[$key]['name'] = $color_list_translate->name??$color_list['name'];
                    $color_lists[$key]['id'] = $color_list['id'];
                }
                return $color_lists;
                break;
            case 'option':
                $option_lists = Options::select('id', 'name', 'icon')->get();
                foreach ($option_lists as $key => $option_list){
                    $color_list_translate = DB::table('yy_options as Option')
                        ->leftJoin('yy_option_translations as OptionT', 'Option.id', '=', 'OptionT.option_id')
                        ->where('Option.id', $option_list['id'])
                        ->where('OptionT.lang', $lang)
                        ->select('Option.id as id', 'OptionT.name as name')->first();

                    $option_lists[$key]->name = $color_list_translate->name ?? $option_list->name;
                    $option_lists[$key]->id = $color_list_translate->id ?? $option_list->id;
                }
                return $option_lists;
                break;
            default:
                # code...
                break;
        }
        
        // dd($from_to);

    }
}
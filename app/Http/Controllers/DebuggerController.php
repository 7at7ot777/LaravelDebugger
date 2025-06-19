<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Debug;
use App\Models\Json;
use App\Models\Number;
use App\Models\Text;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DebuggerController extends Controller
{
    public function display($variable)
    {
        $debug_backtrace = debug_backtrace()[0];
        $class_name = str_replace(base_path(), '', $debug_backtrace['file']);
        $line = $debug_backtrace['line'];

        if (is_array($variable) || is_object($variable)) {
            $encoded = json_encode($variable);
            if ($encoded === false) {
                // json_encode failed, provide a fallback message as an array, then encode again
                $encoded = json_encode(['error' => 'Invalid JSON or Array']);
            }
            // Use the already encoded string, no need to encode again
            $json = Json::create(['json' => $encoded]);
            $morph_type = 'json';
            $morph_id = $json->id;

        } elseif (is_int($variable) || is_float($variable) || is_numeric($variable) || is_bool($variable)) {
            $is_int = is_int($variable);
            $number = $variable;

            if (is_bool($variable)) {
                $number = $variable ? 1 : 0; // Convert boolean to integer
                $is_int = true;
            } elseif (is_float($variable)) {
                $number = (float)$variable;
            }

            $numberModel = Number::create([
                'number' => $number,
                'is_int' => $is_int,
            ]);

            $morph_type = 'number';
            $morph_id = $numberModel->id;

        } elseif (is_string($variable)) {
            $text = Text::create(['text' => $variable]);
            $morph_type = 'text';
            $morph_id = $text->id;

        } else {

            $morph_id = null;
            $morph_type = null;
        }

        Debug::create([
            'class_name' => $class_name,
            'line_number' => $line,
            'debug_id' => $morph_id,
            'debug_type' => $morph_type,
        ]);
    }

    public function displayQuery(Builder $query)
    {
        $debug_backtrace = debug_backtrace()[0];
        $class_name = str_replace(base_path(), '', $debug_backtrace['file']);
        $line = $debug_backtrace['line'];
        $sql = $query->toSql();
        foreach ($query->getBindings() as $binding) {
            $value = is_numeric($binding) ? $binding : "'$binding'";
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }
        $text = Text::create([
            'text' => $sql,
        ]);



        Debug::create([
            'class_name' => $class_name,
            'line_number' => $line,
            'debug_id' => $text->id,
            'debug_type' => 'text',
        ]);

    }
    public function getStackTrace()
    {
        $debug = Debug::with('debugable')
            ->orderBy('id', config('debugger.sort'))
            ->get();
        return $debug;
    }
    private function refreshDB()
    {
        if(config('debugger.refresh_database'))
        {
            Text::truncate();
            Json::truncate();
            Number::truncate();
            Debug::truncate();
        }
    }
}

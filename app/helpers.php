<?php

if (!function_exists('debug_log')) {
    /**
     * Debug helper function for easy debugging
     */
    function debug_log($variable, $label = null)
    {
        $controller = new \App\Http\Controllers\DebuggerController();

        // If a label is provided, create an array with the label
        if ($label) {
            $variable = [$label => $variable];
        }

        $controller->display($variable);

        return $variable; // Return the original variable for chaining
    }
}


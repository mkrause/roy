<?php

/**
 * Application-specific helper class.
 */
class Example
{
    public static function title($layout)
    {
        $site_name = Roy::config('example.longname');
        $page_title = isset($layout->title) ? $layout->title : false;
        
        $result = '';
        $result .= $site_name;
        if ($page_title !== false) {
            $result .= ' - ' . $page_title;
        }
        
        return $result;
    }
    
    public static function stylesheets($layout)
    {
        if (!isset($layout->stylesheets)) {
            return '';
        }
        
        $result = '';
        $stylesheets = $layout->stylesheets;
        
        foreach ($stylesheets as $stylesheet_info) {
            $stylesheet = $stylesheet_info[0];
            $media = isset($stylesheet_info[1]) ? $stylesheet_info[1]
                : 'screen';
            
            $url = path::concat(
                Roy::config('example.base_urls.stylesheets'),
                $stylesheet
            );
            $result .= "<link rel=\"stylesheet\" "
                . "href=\"{$url}\" media=\"{$media}\">\n";
        }
        
        return $result;
    }
    
    public static function scripts($layout)
    {
        if (!isset($layout->scripts)) {
            return '';
        }
        
        $result = '';
        $scripts = $layout->scripts;
        
        foreach ($scripts as $script_info) {
            $script = $script_info[0];
            $url = path::concat(
                Roy::config('example.base_urls.scripts'),
                $script
            );
            $result .= "<script src=\"{$url}\"></script>\n";
        }
        
        return $result;
    }
}

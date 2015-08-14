<?php

/**
 * Resource Handler return a response with a requested css or javascript resource
 *
 * @version $Revision: 1.11 $
 * @package utils
 * @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later
 * @copyright (c) 2015 uzERP LLP (support#uzerp.com). All rights reserved.
 **/
class ResourceHandler
{

    protected $version = '$Revision: 1.11 $';

    /**
     * Return a css or js from the cache, or build and cache the resource
     *
     * @param string $type
     * @param mixed $resources
     * @param string $theme
     */
    function build($type, $resources, $theme = 'default')
    {
        
        // set a few variables
        $cache = TRUE;
        $minify = TRUE;
        $cache_dir = FILE_ROOT . 'data/resource_cache/';
        $last_modified = 0;
        $contents = '';
        $encoding_types = array(
            'js' => 'text/javascript',
            'css' => 'text/css'
        );
        
        // ensure constants are defined, if they aren't chances are they should be false
        // whilst constants are used for the configurable settings, we do come checking
        // and then set some equivilent variables.
        
        // check if the cache flag is true, and check if the cache dir is writable
        $cache = (is_writable($cache_dir) && (get_config('CACHE_RESOURCES') === TRUE));
        
        // check if the minify flag is set
        $minify = (get_config('MINIFY_RESOURCES') === TRUE);
        
        // the resources variable should be an array, but might be passed as a string
        if (! is_array($resources)) {
            $resources = array(
                $resources
            );
        }
        
        // set a default http status of 200
        $status = 200;
        
        // collect last modified times from the resources, keeping the newest one
        // whilst checking if the file exists, if any are throw a 404
        foreach ($resources as $key => $file) {
            
            if ($file === FALSE) {
                unset($resources['$key']);
                continue;
            }
            
            if (file_exists($file)) {
                
                $file_time = filemtime($file);
                
                if ($file_time > $last_modified) {
                    $last_modified = $file_time;
                }
            } else {
                
                $status = 404;
                
                // we can use <br /> as as far as the browser is concerned we're sending html
                echo "/* File does not exist: " . $file . " */" . "<br />";
            }
        }
        
        if ($status === 404) {
            header("HTTP/1.0 404 Not Found");
            exit();
        }
        
        // generate hash of resources array, include last modified to force a new version
        $hash = md5(serialize($resources) . $last_modified);
        
        // generate etag string
        $etag = $last_modified . '-' . $hash;
        
        // check if HTTP_IF_NONE_MATCH matched etag string
        if ($cache && isset($_SERVER['HTTP_IF_NONE_MATCH']) && stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) == '"' . $etag . '"') {
            // return visit and no modifications, send nothing but a 304
            header("HTTP/1.0 304 Not Modified");
            header('Content-Length: 0');
            exit();
        }
        
        // send etag header
        header("Etag: \"" . $etag . "\"");
        
        // if cache is true...
        if ($cache) {
            
            // check if cache file exists...
            $cache_path = $cache_dir . $hash . "." . $type;
            
            if (file_exists($cache_path)) {
                
                // and send it to the browser
                if ($fp = fopen($cache_path, 'rb')) {
                    
                    header("Content-Type: " . $encoding_types[$type]);
                    header("Content-Length: " . filesize($cache_path));
                    fpassthru($fp);
                    fclose($fp);
                    exit();
                }
            }
        }
        
        // if we've reached this point we either didn't want the cache or it didn't exist
        
        // loop through each file...
        foreach ($resources as $file) {
            
            // get the file extension...
            $file_extension = pathinfo($file, PATHINFO_EXTENSION);
            
            // get the file contents...
            $file_contents = file_get_contents($file);
            
            // if we're dealing with a less file, parse and convert it
            if ($file_extension == 'less') {
                $file_contents = str_replace('{THEME_PATH}', THEME_ROOT . "$theme/css/", $file_contents);
                
                // instantiate less...
                $lc = new lessc();
                try {
                    $file_contents = $lc->compile($file_contents);
                } catch (exception $ex) {
                    // failed to compile, spit 500 and make sure the response isn't cached by browsers/proxies
                    http_response_code(500);
                    header('Cache-Control: no-cache, no-store, must-revalidate');
                    header('Pragma: no-cache');
                    header('Expires: 0');
                    echo "Failed to compile less file - " . $ex->getMessage();
                    exit();
                }
                
                // and parse the file
                $file_contents = $lc->compile($file_contents);
            }
            
            // if applicable, minify the contents...
            if ($minify && $type == 'css') {
                $file_contents = ResourceHandler::minifyCSS($file_contents);
            }
            
            // concatinate file path and contents to var
            $contents .= "/** " . $file . " **/" . "\n";
            $contents .= $file_contents . "\n\n";
        }
        
        // send content type and length headers
        header("Content-Type: " . $encoding_types[$type]);
        header('Content-Length: ' . strlen($contents));
        echo $contents;
        
        // if cache is true...
        if ($cache) {
            
            // write cache file
            if ($fp = fopen($cache_path, 'wb')) {
                fwrite($fp, $contents);
                fclose($fp);
            }
        }
        
        exit();
    }

    /**
     * Minify a css resource
     *
     * @param mixed $css
     */
    protected function minifyCSS($css)
    {
        
        // remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // remove tabs, spaces, newlines, etc.
        $css = str_replace(array(
            "\r\n",
            "\r",
            "\n",
            "\t",
            '  ',
            '    ',
            '    '
        ), '', $css);
        
        return $css;
    }
}
?>

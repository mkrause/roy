<?php
/*
 * Roy PHP Framework
 * Copyright 2011 Maikel Krause (maikelkrause.com).
 * Licensed under the MIT license.
 */

/**
 * Provides functionality for filesystem path manipulation.
 */
class Path
{
    /**
     * Convert a path to a normalized form. That is, using *nix-style forward
     * slashes and no trailing slash. E.g. 'foo\bar.php' => 'foo/bar.php'.
     */
    public static function normalize($path)
    {
        if ($path === '/') {
            $normalized = '/';
        } else {
            $normalized = rtrim(str_replace('\\', '/', $path), '/');
        }
        
        // Get rid of double slashes
        $normalized = preg_replace('#//+#', '/', $normalized);
        
        return $normalized;
    }
    
    /**
     * Given a filesystem path, return the absolute, normalized path if the
     * path points to an existing file or directory. Return false otherwise.
     */
    public static function real($path)
    {
        $real = realpath($path);
        
        if ($real !== false) {
            $real = self::normalize($real);
        }
        
        return $real;
    }
    
    /**
     * Intelligently concatenate several path segments.
     *
     * @param [segment1, segment2, ...] List of path segments strings.
     * @return The concatenated path, normalized.
     */
    public static function concat()
    {
        $args = func_get_args();
        $segments = array();
        
        foreach ($args as $key => $arg) {
            $normalized = self::normalize($arg);
            
            // Ignore empty segments and non-initial slashes
            if ($normalized === '' or ($key > 0 and $normalized === '/')) {
                continue;
            }
            
            $segments[] = $normalized;
        }
        
        return self::normalize(implode('/', $segments));
    }
}

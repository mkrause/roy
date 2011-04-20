<?php
/*
 * Roy PHP Framework
 * Copyright 2011 Maikel Krause (maikelkrause.com).
 * Licensed under the MIT license.
 */

/**
 * Encapsulate a piece of text. Provides support for localization and
 * selectively encoding unsafe input.
 */
class Text
{
    /**
     * The format string.
     */
    protected $format;
    
    /**
     * Any format arguments.
     */
    protected $args = array();
    
    /**
     * A mapping of indices to booleans, indicating whether or not the
     * corresponding text component (format string or arguments) should be
     * considered 'safe' (i.e. won't be encoded). Index 0 is the format
     * string, and index i (with i > 0) is the i-th argument.
     */
    protected $safe = array();
    
    public function __construct($format)
    {
        if (func_num_args() > 1 and is_array(func_get_arg(1))) {
            $args = func_get_arg(1);
        } else {
            $args = func_get_args();
            array_shift($args);
        }
        
        $this->format = $format;
        $this->args = $args;
        
        // Mark the format string safe, the rest unsafe
        $this->safe = array();
        $this->mark_unsafe(-1);
        $this->mark_safe(0);
    }
    
    protected function _mark($index, $safe)
    {
        $index = (int)$index;
        $safe = (bool)$safe;
        
        if ($index < -1 or $index > count($this->args)) {
            throw new ProgrammerException("Index '%s' out of bounds", $index);
        }
        
        if ($index === -1) {
            $this->safe[0] = $safe;
            foreach ($this->args as $key => &$arg) {
                $this->safe[$key+1] = $safe;
            }
        } else {
            $this->safe[$index] = $safe;
        }
    }
    
    /**
     * Mark the text component with index $index as 'safe'. This means it
     * will not be encoded when rendered. Possible indices:
     * - -1 = all
     * - 0 = format string
     * - i = the i-th argument
     */
    public function mark_safe($index)
    {
        $this->_mark($index, true);
    }
    
    /**
     * Mark the text component with index $index as 'unsafe'. This means it
     * will be encoded when rendered. Possible indices:
     * - -1 = all
     * - 0 = format string
     * - i = the i-th argument
     */
    public function mark_unsafe($index)
    {
        $this->_mark($index, false);
    }
    
    /**
     * Render as a string. If $encoder is a callback function, use it to
     * encode all 'unsafe' text components.
     */
    public function render($encoder = null)
    {
        $format = $this->format;
        $args = $this->args;
        
        if (is_callable($encoder)) {
            if (!$this->safe[0]) {
                $format = $encoder($format);
            }
            
            foreach ($args as $key => &$arg) {
                if (!$this->safe[$key + 1]) {
                    $arg = $encoder($arg);
                }
            }
        }
        
        return vsprintf($format, $args);
    }
    
    public function __toString()
    {
        return $this->render();
    }
}

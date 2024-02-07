<?php

class BaseClass
{
    public bool $debug = false;
    public function echoDebugMessage ($text): void
    {
        if ($this->debug) {
            echo $text.PHP_EOL;
        }
    }
}
<?php

namespace Prophet777\MoonWalk\Parser;

interface ParserInterface
{
    /**
     * @param string $line
     */
    public function parse($line);

}
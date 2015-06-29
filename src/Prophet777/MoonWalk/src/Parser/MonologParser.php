<?php

namespace Prophet777\MoonWalk\Parser;

class MonologParser implements ParserInterface
{
    protected $pattern = '/\[(?P<date>.*)\] (?P<logger>\w+).(?P<level>\w+): (?P<message>.*[^ ]+) (?P<context>[^ ]+) (?P<extra>[^ ]+)/';

    /**
     * Constructor
     * @param string $pattern
     */
    public function __construct($pattern = null)
    {
        $this->pattern = ($pattern) ?: $this->pattern;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($line)
    {
        if( !is_string($line) || strlen($line) === 0) {
            return array();
        }

        preg_match($this->pattern, $line, $data);

        if (!isset($data['date'])) {
            return array();
        }

        return array(
            'date' => \DateTime::createFromFormat('Y-m-d H:i:s', $data['date']),
            'logger' => $data['logger'],
            'level' => $data['level'],
            'message' => $data['message'],
            'context' => json_decode($data['context'], true),
            'extra' => json_decode($data['extra'], true)
        );
    }
}
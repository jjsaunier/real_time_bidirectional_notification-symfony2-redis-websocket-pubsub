<?php

namespace Prophet777\MoonWalk\Parser;

class MonologParser implements ParserInterface
{
    protected $pattern = array(
        'default' => '/\[(?P<date>.*)\] (?P<logger>\w+).(?P<level>\w+): (?P<message>[^\[\{]+) (?P<context>[\[\{].*[\]\}]) (?P<extra>[\[\{].*[\]\}])/',
        'error'   => '/\[(?P<date>.*)\] (?P<logger>\w+).(?P<level>\w+): (?P<message>(.*)+) (?P<context>[^ ]+) (?P<extra>[^ ]+)/'
    );

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

        preg_match($this->pattern['default'], $line, $data);

        if (!isset($data['date'])) {
            return array();
        }

        $context = (array) json_decode($data['context'], true);
        $extra = (array) json_decode($data['extra'], true);

        $result = array(
            'date' => \DateTime::createFromFormat('Y-m-d H:i:s', $data['date'])->format(\DateTime::W3C),
            'logger' => $data['logger'],
            'level' => $data['level'],
            'message' => $data['message']
        );

        if(!empty($context)){
            $result['context'] = $context;
        }

        if(!empty($extra)){
            $result['extra'] = $extra;
        }

        return $result;
    }
}
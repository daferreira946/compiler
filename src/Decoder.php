<?php

namespace Compiler\src;

class Decoder
{
    private array $config;

    public function __construct()
    {
        $json = file_get_contents("../config.json");
        $this->config = json_decode($json, true);
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}

<?php

namespace ThermalPrint\Connectors;

use ThermalPrint\Connectors\PrintConnector;

final class DummyConnector implements PrintConnector
{
    /**
     * @var array Buffer of accumulated data.
     */
    private $buffer;

    /**
     * Create new print connector
     */
    public function __construct()
    {
        $this->buffer = array();
    }

    public function __destruct()
    {
        if ($this->buffer !== null) {
            trigger_error(
                "O conector de impressão não foi finalizado. Você esqueceu de fechar a conexão com a impressora?",
                E_USER_NOTICE
            );
        }
    }
    
    /**
     * finaliza limpando o buffer
     */
    public function finalize()
    {
        $this->buffer = null;
    }

    /**
     * @return string Get the accumulated data that has been sent to this buffer.
     */
    public function getData()
    {
        return implode($this->buffer);
    }
    
    /**
     * writes in buffer
     * @param string $data
     */
    public function write($data)
    {
        $this->buffer[] = $data;
    }
}

<?php

/**
 * Config - Configuration Class
 * Type : Class
 *        This file is used to set a config file to be used in the system.
 * Example: $c = new Config();
 *          $c->setFile('config.xml');
 * @package		dbms
 * @author              Jose Marcelius Hipolito <hi@joeyhipolito.com>
 * @license             University of the East Research and Development Unit
 * @copyright           Copyright (c) 2013
 */

class Config {

    private $configFile = 'app/config.xml';
    private $items = array();

    public function __construct() {
        $this->parse();
    }

    public function __get($id) {
        return $this->items[$id];
    }


    public function parse() {
        $doc = new DOMDocument();
        $doc->load($this->configFile);

        $cn = $doc->getElementsByTagName('config');
        $nodes = $cn->item(0)->getElementsByTagName('*');
        foreach ($nodes as $node) {
            $this->items[$node->nodeName] = $node->nodeValue;
        }
    }

    public function save() {
        $doc = new DOMDocument();
        $doc->formatOutput = true;

        $r = $doc->createElement('config');
        $r->appendChild($r);

        foreach ($this->items as $key => $value) {
            $kn = $doc->createElement($key);
            $kn->appendChild($doc->createTextNode($value));
            $r->appendChild($kn);
        }

        copy($this->configFile, $this->configFile . '.bak');
        $doc->save($this->configFile);

    }

}

/* End of file Config.php */
<?php

require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/src/autoload.php');

class MosparoPlugin extends Plugin
{
    var $config_class = 'MosparoOsTicket\\Config';

    function bootstrap()
    {
        \MosparoOsTicket\Field::$pluginConfig = $this->getConfig();
        FormField::addFieldTypes(__('Verification'), function () {
            return array(
                'mosparo' => array('mosparo', 'MosparoOsTicket\\Field')
            );
        });
    }
}

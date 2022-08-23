<?php

namespace MosparoOsTicket;

use BooleanField;
use Messages;
use PluginConfig;
use SectionBreakField;
use TextboxField;

class Config extends PluginConfig
{
    function getOptions()
    {
        $privateKeyRequired = true;
        $config = $this->getInfo();
        if (isset($config['mosparoPrivateKey']) && $config['mosparoPrivateKey'] !== '') {
            $privateKeyRequired = false;
        }

        return [
            '_' => new SectionBreakField([
                'hint' => 'Please fill in the values below. You can find the values in the settings of your mosparo project.'
            ]),
            'mosparoHost' => new TextboxField([
                'id' => 'mosparoHost',
                'label' => 'Host',
                'configuration' => [
                    'size' => 59,
                    'length' => 255
                ],
                'required' => true,
            ]),
            'mosparoUuid' => new TextboxField([
                'id' => 'mosparoUuid',
                'label' => 'UUID',
                'configuration' => [
                    'size' => 59,
                    'length' => 255
                ],
                'required' => true,
            ]),
            'mosparoPublicKey' => new TextboxField([
                'id' => 'mosparoPublicKey',
                'label' => 'Public key',
                'configuration' => [
                    'size' => 59,
                    'length' => 255
                ],
                'required' => true,
            ]),
            'mosparoPrivateKey' => new TextboxField([
                'id' => 'mosparoPrivateKey',
                'label' => 'Secret Key',
                'configuration' => [
                    'size' => 59,
                    'length' => 255
                ],
                'required' => $privateKeyRequired,
                'widget' => 'PasswordWidget',
                'validator' => 'noop',
            ]),
            'mosparoVerifySsl' => new BooleanField([
                'id' => 'mosparoVerifySsl',
                'label' => 'Verify SSL',
                'default' => true,
            ]),
            'mosparoLoadCssResourceOnInitialization' => new BooleanField([
                'id' => 'mosparoLoadCssResourceOnInitialization',
                'label' => 'Load CSS resource on initialization',
            ]),
        ];
    }

    function pre_save(&$config, &$errors)
    {
        if (!function_exists('curl_init')) {
            Messages::error('Required module curl not found.');

            return false;
        }

        if (!isset($config['mosparoPrivateKey']) || $config['mosparoPrivateKey'] == '') {
            unset($config['mosparoPrivateKey']);
        }

        return true;
    }
}

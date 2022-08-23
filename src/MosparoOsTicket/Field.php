<?php

namespace MosparoOsTicket;

use FormField;
use Mosparo\ApiClient\Client;
use Mosparo\ApiClient\Exception;

class Field extends FormField
{
    static $widget = 'MosparoOsTicket\\MosparoWidget';
    static $pluginConfig;

    public function getPluginConfig()
    {
        return static::$pluginConfig;
    }

    public function validateEntry($value)
    {
        static $validation = array();

        parent::validateEntry($value);

        $id = $this->get('id');
        $validationData = &$validation[$id];

        $data = $this->getSource();
        if ($data) {
            [ $data, $mosparoSubmitToken, $mosparoValidationToken ] = $this->cleanData($data);

            $data = $this->convertData($data);

            $pluginConfig = $this->getPluginConfig()->getInfo();
            $client = new Client(
                $pluginConfig['mosparoHost'],
                $pluginConfig['mosparoPublicKey'],
                $pluginConfig['mosparoPrivateKey'],
                [
                    'verify' => (($pluginConfig['mosparoVerifySsl'] ?? true) == 1),
                ]
            );

            try {
                $result = $client->validateSubmission($data, $mosparoSubmitToken, $mosparoValidationToken);
                $validationData['valid'] = $result;

                if (!$result) {
                    $validationData['errors'] = ['Form submission not valid.'];
                }
            } catch (Exception $e) {
                $validationData['errors'] = [ $e->getMessage() ];
                $validationData['valid'] = false;
            }
        } else {
            $validationData['errors'] = ['No data found.'];
            $validationData['valid'] = false;
        }

        if (!$validation[$id]['valid']) {
            foreach ($validation[$id]['errors'] as $e) {
                $this->_errors[] = $e;
            }
        }
    }

    protected function cleanData($data)
    {
        $mosparoSubmitToken = $data['_mosparo_submitToken'] ?? null;
        $mosparoValidationToken = $data['_mosparo_validationToken'] ?? null;

        foreach ($data as $key => $value) {
            if (strpos($key, '_mosparo_') === 0) {
                unset($data[$key]);
            }
        }

        unset($data['a']);
        unset($data['__CSRFToken__']);
        unset($data['files']);
        unset($data['draft_id']);
        unset($data['emailId']);
        unset($data['deptId']);
        unset($data['message']);

        $ignoredTypes = Filter::getInstance()->applyFilter('ignoredTypes', [
            \FileUploadWidget::class,
            \CheckboxWidget::class,
            \SectionBreakWidget::class,
            \FreeTextWidget::class,
            MosparoWidget::class,
        ]);
        foreach ($this->getForm()->getFields() as $field) {
            if (in_array(get_class($field->getWidget()), $ignoredTypes)) {
                unset($data[$field->getWidget()->name]);
            }
        }

        return [ $data, $mosparoSubmitToken, $mosparoValidationToken ];
    }

    protected function convertData($data)
    {
        $convertedData = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    if (is_numeric($subKey)) {
                        $convertedData[$key . '[]'] = $subValue;
                    } else {
                        $convertedData[$key . '[' . $subKey . ']'] = $subValue;
                    }
                }
            } else {
                $convertedData[$key] = $value;
            }
        }

        return $convertedData;
    }

    public function getConfigurationOptions()
    {
        return [
            '_' => new DescriptionField([
                'label' => 'Design',
                'fieldDescription' => 'Please configure the design for the mosparo box in the mosparo project settings.'
            ]),
        ];
    }
}
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
        global $osTicketOtherScriptSources;

        $config = static::$pluginConfig->getInfo();

        if ($config !== null && isset($config['mosparoHost'])) {
            if (!isset($osTicketOtherScriptSources)) {
                $osTicketOtherScriptSources = [];
            }

            $osTicketOtherScriptSources[] = $config['mosparoHost'];
        }

        return static::$pluginConfig;
    }

    function isBlockLevel() {
        return true;
    }

    public function validateEntry($value)
    {
        static $validation = array();

        parent::validateEntry($value);

        // Do not execute the mosparo validation if the field is invisible (in example for the staff)
        if (!$this->isMosparoVisible()) {
            return;
        }

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
                $validationData['valid'] = $result->isSubmittable();

                if (!$result->isSubmittable()) {
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

    public function isMosparoVisible(): bool
    {
        global $thisstaff;

        $mode = 'client';
        if ($thisstaff != null && $thisstaff instanceof \Staff) {
            $mode = 'staff';
        }

        $visible = false;
        if ($mode === 'staff' && $this->isVisibleToStaff()) {
            $visible = true;
        }

        if ($mode === 'client' && $this->isVisibleToUsers()) {
            $visible = true;
        }

        return $visible;
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
                        $convertedData[$key][] = $subValue;
                    } else {
                        $convertedData[$key][$subKey] = $subValue;
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
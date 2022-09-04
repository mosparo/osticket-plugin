<?php

namespace MosparoOsTicket;

use Form;
use Widget;

class MosparoWidget extends Widget
{
    function render()
    {
        $fieldConfig = $this->field->getConfiguration();
        $pluginConfig = $this->field->getPluginConfig()->getInfo();

        $options = [
            'loadCssResource' => $pluginConfig['mosparoLoadCssResourceOnInitialization']
        ];

        $host = $pluginConfig['mosparoHost'];
        $uuid = $pluginConfig['mosparoUuid'];
        $publicKey = $pluginConfig['mosparoPublicKey'];

        if (!$pluginConfig['mosparoLoadCssResourceOnInitialization']) {
            Form::emitMedia(sprintf('%s/resources/%s.css', $host, $uuid), 'css');
        }

        $instanceId = uniqid();
        ?>

        <div id="mosparo-box-<?php echo $instanceId; ?>"></div>
        <script>
            (function () {
                var script = document.createElement("script");
                script.type = "text/javascript";
                script.src = '<?php echo sprintf('%s/build/mosparo-frontend.js', $host); ?>';

                script.onload = function() {
                    let formEl = document.getElementById("mosparo-box-<?php echo $instanceId; ?>");
                    let options = <?php echo json_encode($options); ?>;

                    new mosparo("mosparo-box-<?php echo $instanceId; ?>", "<?php echo $host; ?>", "<?php echo $uuid; ?>", "<?php echo $publicKey; ?>", options);

                    $(document).on('blur', 'div.redactor-box', function (ev) {
                        let textarea = this.getElementsByTagName('textarea')[0];
                        textarea.textContent = $R(textarea, 'source.getCode');
                        textarea.dispatchEvent(new Event('change'));
                    });

                    $(document).on('submit-aborted', 'form', function (ev) {
                        $('#overlay, #loading').hide();
                    });
                };

                document.getElementsByTagName("head")[0].appendChild(script);
            })();
        </script>

        <?php
    }

    function getValue()
    {
        if (!($data = $this->field->getSource())) {
            return null;
        }

        if (!isset($data['_mosparo_submitToken']) || !isset($data['_mosparo_validationToken'])) {
            return null;
        }

        return true;
    }
}
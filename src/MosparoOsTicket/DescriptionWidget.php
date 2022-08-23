<?php

namespace MosparoOsTicket;

use Format;
use Widget;

class DescriptionWidget extends Widget
{
    public function render($options = [])
    {
        ?>
            <div class="form-header section-break">
                <em><?php echo Format::display($this->field->getLocal('fieldDescription')); ?></em>
            </div>
        <?php
    }
}
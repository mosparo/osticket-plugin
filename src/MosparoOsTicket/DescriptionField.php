<?php

namespace MosparoOsTicket;

use FormField;

class DescriptionField extends FormField
{
    static $widget = 'MosparoOsTicket\\DescriptionWidget';

    function hasData() {
        return false;
    }

    function isBlockLevel() {
        return true;
    }
}
&nbsp;
<p align="center">
    <img src="https://github.com/mosparo/mosparo/blob/master/assets/images/mosparo-logo.svg?raw=true" alt="mosparo logo contains a bird with the name Mo and the mosparo text"/>
</p>

<h1 align="center">
    for osTicket
</h1>
<p align="center">
    This osTicket plugins adds the required functionality to use mosparo in your osTicket forms.
</p>

-----

## Description

The osTicket plugin adds the required functionality to your osTicket installation to use mosparo in your forms.

## How to use

Please see our [How to use](https://mosparo.io/how-to-use/) introduction on our website to learn how to use mosparo in your form.

In step 3 of the how-to-use explanation, you must integrate mosparo into your osTicket. Please follow the [Installation](#installation) part below for this process.

## Requirements

To use the plugin you need the following requirements:

- osTicket >= 1.17.x
- PHP >= 8.1
- Access to the files of your osTicket installation
- Administration access in osTicket
- mosparo installation

## Installation

To use the plugin, please follow this installation instruction:

1. Download the plugin from the release page on GitHub
2. Extract the files
3. Copy the extracted folder `mosparo-osticket` in the directory `include/plugins` inside your osTicket installation. The full path should look like `include/plugins/mosparo-osticket`.
4. Go to the osTicket administration area
5. Go to `Manage` > `Plugins`
6. Enable the `mosparo Plugin`
7. Click on the plugin name to edit the plugin settings
8. Fill in the information for your mosparo project
9. Go to `Manage` > `Forms`
10. Edit the form where you want to add the mosparo field
11. Choose `mosparo` in the `Type` column 
12. osTicket added enhanced Content Security Policy headers in v1.17.5 and v1.18.1. This is a very good decision, but since there is no option for mosparo to add the mosparo host, you have to adjust the osTicket file manually. Open the file `include/client/header.inc.php` and locate the following code:
```php
header("Content-Type: text/html; charset=UTF-8");
header("Content-Security-Policy: frame-ancestors ".$cfg->getAllowIframes()."; script-src 'self' 'unsafe-inline'; object-src 'none'");
```
13. Replace this code with the following code:
```php
$otherScriptSrc = '';
if (isset($osTicketOtherScriptSources)) {
    $otherScriptSrc = implode(' ', $osTicketOtherScriptSources);
}

header("Content-Type: text/html; charset=UTF-8");
header("Content-Security-Policy: frame-ancestors ".$cfg->getAllowIframes()."; script-src 'self' 'unsafe-inline' ".$otherScriptSrc."; object-src 'none'");
```
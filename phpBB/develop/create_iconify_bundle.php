<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* This file creates Iconify bundle JS file in assets/iconify directory.
* See https://docs.iconify.design/icon-components/bundles/examples/svg-framework-custom.html
* iconify/json-tools and iconify/json dev requirements should be installed for the script to work.
*/

define('IN_PHPBB', true);
$phpbb_root_path = dirname(__FILE__) . '/../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

include($phpbb_root_path . 'vendor/autoload.php');
include($phpbb_root_path . 'includes/constants.' . $phpEx);
require($phpbb_root_path . 'phpbb/class_loader.' . $phpEx);
$phpbb_class_loader = new \phpbb\class_loader('phpbb\\', "{$phpbb_root_path}phpbb/", $phpEx);
$phpbb_class_loader->register();

use Iconify\JSONTools\Collection;
use Iconify\IconsJSON\Finder;

// JS file to save bundle to
$target = $phpbb_root_path . 'assets/iconify/iconify-bundle.js';

// Icons to bundle, the list of iconify icons used in phpBB
$icons = [
	'fa:angle-left',
	'fa:angle-right',
	'fa:arrow-right',
	'fa:bar-chart',
	'fa:bars',
	'fa:bars',
	'fa:bell',
	'fa:caret-down',
	'fa:check-circle',
	'fa:chevron-circle-up',
	'fa:chevron-left',
	'fa:chevron-right',
	'fa:circle',
	'fa:clone',
	'fa:code',
	'fa:cog',
	'fa:cogs',
	'fa:dot-circle-o',
	'fa:envelope-o',
	'fa:exclamation',
	'fa:exclamation-triangle',
	'fa:external-link',
	'fa:external-link-square',
	'fa:file',
	'fa:file-o',
	'fa:gavel',
	'fa:group',
	'fa:home',
	'fa:inbox',
	'fa:info',
	'fa:key',
	'fa:lock',
	'fa:mail-forward',
	'fa:paperclip',
	'fa:pencil',
	'fa:pencil-square-o',
	'fa:power-off',
	'fa:print',
	'fa:question',
	'fa:question-circle',
	'fa:quote-left',
	'fa:recycle',
	'fa:refresh',
	'fa:reply',
	'fa:rss-square',
	'fa:rss-square',
	'fa:search',
	'fa:share-alt',
	'fa:shield',
	'fa:sliders',
	'fa:sort-amount-asc',
	'fa:times-circle',
	'fa:tint',
	'fa:trash',
	'fa:user',
	'fa:wrench',
	'ic:outline-directions',
	'ic:outline-print',
	'mdi:alert-decagram',
	'mdi:arrow-right-bold',
	'mdi:at',
	'mdi:bookmark-plus-outline',
	'mdi:comment-account-outline',
	'mdi:comment-processing-outline',
	'mdi:comment-text-outline',
	'mdi:earth',
	'mdi:eye-minus-outline',
	'mdi:eye-plus-outline',
	'mdi:facebook-box',
	'mdi:file',
	'mdi:file-account-outline',
	'mdi:file-document-box-multiple-outline',
	'mdi:file-document-box-outline',
	'mdi:file-document-outline',
	'mdi:file-hidden',
	'mdi:file-question-outline',
	'mdi:file-upload-outline',
	'mdi:fire',
	'mdi:format-bold',
	'mdi:format-italic',
	'mdi:format-list-bulleted',
	'mdi:format-list-numbered',
	'mdi:format-quote-open',
	'mdi:format-underline',
	'mdi:image-outline',
	'mdi:info-variant',
	'mdi:link-variant',
	'mdi:lock',
	'mdi:lock',
	'mdi:playlist-add',
	'mdi:send-circle-outline',
	'mdi:skype',
	'mdi:star',
	'mdi:twitter',
	'mdi:update',
	'mdi:youtube',
];

// Organize icons by prefix
$icons = organizeIconsList($icons);

// Load icons data
$output = '';
foreach ($icons as $prefix => $iconsList) {
   // Load icon set
   $collection = new Collection($prefix);
   if (!$collection->loadIconifyCollection($prefix)) {
       throw new Error(
           'Icons with prefix "' . $prefix . '" do not exist in Iconify. Update iconify/json?'
       );
   }

   // Make sure all icons exist
   foreach ($iconsList as $name) {
       if (!$collection->iconExists($name)) {
           // Uncomment next line to throw error if an icon does not exist
           // throw new Error('Could not find icon: "' . $prefix . ':' . $name . '"');
           echo 'Could not find icon: "', $prefix, ':', $name, "\"\n";
       }
   }

   // Get data for all icons as string
   $output .= $collection->scriptify([
       'icons' => $iconsList,
       'callback' => 'add',
       'optimize' => true,
   ]);
}

// Wrap in custom code that checks for Iconify.addCollection and IconifyPreload
$output = '(function() {
   function add(data) {
       try {
           if (typeof self.Iconify === \'object\' && self.Iconify.addCollection) {
               self.Iconify.addCollection(data);
               return;
           }
           if (typeof self.IconifyPreload === \'undefined\') {
               self.IconifyPreload = [];
           }
           self.IconifyPreload.push(data);
       } catch (err) {
       }
   }
   ' . $output . '
})();' . "\n";

// Save to file
file_put_contents($target, $output);

echo 'Saved ', $target, ' (', strlen($output), " bytes)\n";

/**
* Organize icons list by prefix
*
* Result is an object, where key is prefix, value is array of icon names
*/
function organizeIconsList($icons)
{
   $results = [];

   foreach ($icons as $str) {
       // Split icon to prefix and name
       $icon = stringToIcon($str);
       if ($icon === null || $icon['provider'] !== '') {
           // Invalid name or icon name has provider.
           // All icons in this example are from Iconify, so providers are not supported.
           throw new Error('Invalid icon name: ' . $str);
       }

       $prefix = $icon['prefix'];
       $name = $icon['name'];

       // Add icon to results
       if (!isset($results[$prefix])) {
           $results[$prefix] = [$name];
           continue;
       }
       if (!in_array($name, $results[$prefix])) {
           $results[$prefix][] = $name;
       }
   }

   return $results;
}

/**
* Convert icon name from string to object.
*
* Object properties:
* - provider (ignored in this example)
* - prefix
* - name
*
* This function was converted to PHP from @iconify/utils/src/icon/name.ts
* See https://github.com/iconify/iconify/blob/master/packages/utils/src/icon/name.ts
*/
function stringToIcon($value)
{
   $provider = '';
   $colonSeparated = explode(':', $value);

   // Check for provider with correct '@' at start
   if (substr($value, 0, 1) === '@') {
       // First part is provider
       if (count($colonSeparated) < 2 || count($colonSeparated) > 3) {
           // "@provider:prefix:name" or "@provider:prefix-name"
           return null;
       }
       $provider = substr(array_shift($colonSeparated), 1);
   }

   // Check split by colon: "prefix:name", "provider:prefix:name"
   if (count($colonSeparated) > 3 || !count($colonSeparated)) {
       return null;
   }
   if (count($colonSeparated) > 1) {
       // "prefix:name"
       $name = array_pop($colonSeparated);
       $prefix = array_pop($colonSeparated);
       return [
           // Allow provider without '@': "provider:prefix:name"
           'provider' => count($colonSeparated) > 0 ? $colonSeparated[0] : $provider,
           'prefix' => $prefix,
           'name' => $name,
       ];
   }

   // Attempt to split by dash: "prefix-name"
   $dashSeparated = explode('-', $colonSeparated[0]);
   if (count($dashSeparated) > 1) {
       return [
           'provider' => $provider,
           'prefix' => array_shift($dashSeparated),
           'name' => implode('-', $dashSeparated),
       ];
   }

   return null;
}

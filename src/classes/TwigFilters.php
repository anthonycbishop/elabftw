<?php declare(strict_types=1);
/**
 * @author Nicolas CARPi <nico-git@deltablot.email>
 * @copyright 2023 Nicolas CARPi
 * @see https://www.elabftw.net Official website
 * @license AGPL-3.0
 * @package elabftw
 */

namespace Elabftw\Elabftw;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Elabftw\Enums\Metadata as MetadataEnum;
use Elabftw\Models\Config;
use function implode;
use function is_array;

/**
 * Twig filters
 */
class TwigFilters
{
    /**
     * For displaying messages using bootstrap alerts
     *
     * @param string $message The message to display
     * @param string $type Can be 'ok', 'ko' or 'warning'
     * @param bool $cross do we display a cross or not?
     * @return string the HTML of the message
     */
    public static function displayMessage(string $message, string $type, bool $cross = true): string
    {
        $icon = 'fa-info-circle';
        $alert = 'success';

        if ($type === 'ko') {
            $icon = 'fa-exclamation-triangle';
            $alert = 'danger';
        } elseif ($type === 'warning') {
            $icon = 'fa-chevron-right';
            $alert = $type;
        }

        $crossLink = '';

        if ($cross) {
            $crossLink = "<a href='#' class='close' data-dismiss='alert'>&times;</a>";
        }

        return sprintf("<div class='alert alert-%s'><i class='fa-fw fas %s color-%s'></i>%s %s</div>", $alert, $icon, $alert, $crossLink, $message);
    }

    /**
     * Process the metadata json string into html
     * @psalm-suppress PossiblyUnusedMethod this method is used in twig templates
     */
    public static function formatMetadata(string $json): string
    {
        $final = '';
        $extraFields = (new Metadata($json))->getExtraFields();
        if ($extraFields === null) {
            return $final;
        }
        // sort the elements based on the position attribute. If not set, will be at the end.
        uasort($extraFields, function (array $a, array $b): int {
            return ($a['position'] ?? 9999) <=> ($b['position'] ?? 9999);
        });
        foreach ($extraFields as $key => $properties) {
            $description = isset($properties[MetadataEnum::Description->value])
                ? sprintf('<span class="smallgray">%s</span>', $properties[MetadataEnum::Description->value])
                : '';
            $value = $properties[MetadataEnum::Value->value];
            // checkbox is a special case
            if ($properties[MetadataEnum::Type->value] === 'checkbox') {
                $checked = $properties[MetadataEnum::Value->value] === 'on' ? 'checked' : '';
                $value = '<input class="d-block" disabled type="checkbox" ' . $checked . '>';
            }
            // url is another special case
            if ($properties[MetadataEnum::Type->value] === 'url') {
                $newTab = 'target="_blank" rel="noopener"';
                if (($properties['open_in_current_tab'] ?? false) === true) {
                    $newTab = '';
                }
                $value = '<a href="' . $value . '" ' . $newTab . '>' . $value . '</a>';
            }

            // multi select will be an array
            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            $final .= sprintf('<li class="list-group-item"><h5 class="mb-0">%s</h5>%s<h6>%s</h6></li>', $key, $description, $value);
        }
        return $final;
    }

    public static function decrypt(?string $encrypted): string
    {
        if (empty($encrypted)) {
            return '';
        }
        return Crypto::decrypt($encrypted, Key::loadFromAsciiSafeString(Config::fromEnv('SECRET_KEY')));
    }
}

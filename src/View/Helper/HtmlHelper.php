<?php
namespace lilHermit\Bootstrap4\View\Helper;


use Cake\View\View;

class HtmlHelper extends \Cake\View\Helper\HtmlHelper {

    private $bootstrapTemplates = [
        'breadCrumbOl' => '<ol class="breadcrumb"{{attrs}}>{{content}}</ol>',
        'breadCrumbLi' => '<li class="breadcrumb-item"{{attrs}}>{{content}}</li>',
    ];

    public function __construct(View $View, array $config = []) {
        $this->_defaultConfig['templates'] =
            array_merge($this->_defaultConfig['templates'], $this->bootstrapTemplates);

        parent::__construct($View, $config);
    }

    /**
     * Returns breadcrumbs as a (x)html list formatted for Bootstrap
     *
     * This method uses HtmlHelper::tag() to generate list and its elements. Works
     * similar to HtmlHelper::getCrumbs(), so it uses options which every
     * crumb was added with.
     *
     * ### Options
     *
     * - `separator` Separator content to insert in between breadcrumbs, defaults to ''
     * - `firstClass` Class for wrapper tag on the first breadcrumb, defaults to 'first'
     * - `lastClass` Class for wrapper tag on current active page, defaults to 'last'
     *
     * @param array $options Array of HTML attributes to apply to the generated list elements.
     * @param string|array|bool $startText This will be the first crumb, if false it defaults to first crumb in array. Can
     *   also be an array, see `HtmlHelper::getCrumbs` for details.
     * @return string|null Breadcrumbs HTML list.
     * @link http://book.cakephp.org/3.0/en/views/helpers/html.html#creating-breadcrumb-trails-with-htmlhelper
     * @deprecated 3.3.6 Use the BreadcrumbsHelper instead
     */
    public function getCrumbList(array $options = [], $startText = false) {
        $defaults = ['firstClass' => false, 'lastClass' => 'active', 'separator' => '', 'escape' => true];
        $options += $defaults;
        $firstClass = $options['firstClass'];
        $lastClass = $options['lastClass'];
        $separator = $options['separator'];
        $escape = $options['escape'];
        unset($options['firstClass'], $options['lastClass'], $options['separator'], $options['escape']);

        $crumbs = $this->_prepareCrumbs($startText, $escape);
        if (empty($crumbs)) {
            return null;
        }

        $result = '';
        $crumbCount = count($crumbs);
        $ulOptions = $options;
        foreach ($crumbs as $which => $crumb) {
            $options = [];
            if (empty($crumb[1])) {
                $elementContent = $crumb[0];
            } else {
                $elementContent = $this->link($crumb[0], $crumb[1], $crumb[2]);
            }
            if (!$which && $firstClass !== false) {
                $options['class'] = $firstClass;
            } elseif ($which == $crumbCount - 1 && $lastClass !== false) {
                $options['class'] = $lastClass;
            }
            if (!empty($separator) && ($crumbCount - $which >= 2)) {
                $elementContent .= $separator;
            }
            $result .= $this->formatTemplate('breadCrumbLi', [
                'content' => $elementContent,
                'attrs' => $this->templater()->formatAttributes($options)
            ]);
        }
        return $this->formatTemplate('breadCrumbOl', [
            'content' => $result,
            'attrs' => $this->templater()->formatAttributes($ulOptions)
        ]);
    }

    /**
     * Creates a bootstrap button.
     *
     * If $url starts with "http://" this is treated as an external link. Else,
     * it is treated as a path to controller/action and parsed with the
     * UrlHelper::build() method.
     *
     * If the $url is empty, $title is used instead.
     *
     * ### Options
     *
     * - `size` Can be `small`, `normal` or `large` (default is `normal`)
     * - `class` Any additional css classes
     * - `secondary` Boolean true if you want secondary colour.
     * - `outline` Boolean true if you want button outlined.
     *
     * @param string $title The content to be used for the button.
     * @param string|array|null $url Cake-relative URL or array of URL parameters, or
     *   external URL (starts with http://)
     * @param array $options Array of options and HTML attributes.
     * @return string the element.
     */
    public function button($title, $url = null, array $options = []) {
        $options = $options + [
                'size' => 'normal',
                'type' => 'link',
                'secondary' => false,
                'outline' => false,
                'class' => []
            ];

        // Convert and sanitise the css class
        if (is_array($options['class'])) {
            $options['class'][] = 'btn';
        } else if (is_string($options['class'])) {
            $options['class'] = ['btn', $options['class']];
        } else {
            $options['class'] = ['btn'];
        }

        if ($options['outline']) {
            $options['class'][] = $options['secondary'] ? 'btn-outline-secondary' : 'btn-outline-primary';
        } else {
            $options['class'][] = $options['secondary'] ? 'btn-secondary' : 'btn-primary';
        }

        switch ($options['size']) {
            case 'large':
            case 'lg':
                $options['class'][] = 'btn-large';
                break;
            case 'small':
            case 'sm':
                $options['class'][] = 'btn-sm';
                break;
        }
        unset($options['size'], $options['secondary']);

        if (in_array($options['type'], ['button', 'submit', 'reset'])) {
            return $this->tag('button', $title, $options);
        } else {
            return $this->link($title, $url, $options);
        }
    }

    /**
     * Returns the css tag for bootstrap
     *
     * @param $version string|array string of the version of bootstrap or
     * an array containing 'url' and 'integrity' keys
     *
     * @return string|null the full css tag
     */
    public function bootstrapCss($version = '4.0.0-alpha.6') {
        $versions = [
            '4.0.0-alpha.5' => [
                'url' => 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.5/css/bootstrap.min.css',
                'integrity' => 'sha384-AysaV+vQoT3kOAXZkl02PThvDr8HYKPZhNT5h/CXfBThSRXQ6jW5DO2ekP5ViFdi'
            ],
            '4.0.0-alpha.6' => [
                'url' => 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css',
                'integrity' => 'sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ'
            ]
        ];
        if (is_array($version)) {
            return $this->css($version['url'], [
                'integrity' => $version['integrity'],
                'crossorigin' => 'anonymous'
            ]);
        } else if (array_key_exists($version, $versions)) {
            return $this->css($versions[$version]['url'], [
                'integrity' => $versions[$version]['integrity'],
                'crossorigin' => 'anonymous'
            ]);
        }
        return '';
    }

    /**
     * Returns the script tag to the bootstrap js
     *
     * @param $options array of options
     *
     *   ### Options
     *
     * - `version` The version of bootstrap javascript required. Defaults = latest version
     * - `url` The url of the non built-in version bootstrap javascript
     * - `integrity` The integrity hash for the `url` key
     * - `own` Should we include this plugin javascript too. Default = true
     *
     * @return string The full script tag or blank if `own` is false and `version` doesn't exist
     */
    public function bootstrapScript($options = []) {

        // Add the defaults
        $options += [
            'version' => '4.0.0-alpha.6',
            'own' => true
        ];

        $versions = [
            '4.0.0-alpha.2' => [
                'url' => 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.2/js/bootstrap.min.js',
                'integrity' => 'sha384-vZ2WRJMwsjRMW/8U7i6PWi6AlO1L79snBrmgiDpgIWJ82z8eA5lenwvxbMV1PAh7'
            ],
            '4.0.0-alpha.5' => [
                'url' => 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.5/js/bootstrap.min.js',
                'integrity' => 'sha384-BLiI7JTZm+JWlgKa0M0kGRpJbF2J8q+qreVrKBC47e3K6BW78kGLrCkeRX6I9RoK'
            ],
            '4.0.0-alpha.6' => [
                'url' => 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js',
                'integrity' => 'sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn'
            ]
        ];

        $return = "";
        if (filter_var($options['own'], FILTER_VALIDATE_BOOLEAN)) {
            $return = $this->script('lilHermit/Bootstrap4.form-manipulation.js');
        }

        if (isset($options['url']) && isset($options['integrity'])) {
            $return .= $this->script($options['url'], [
                'integrity' => $options['integrity'],
                'crossorigin' => 'anonymous'
            ]);
        } else if (array_key_exists($options['version'], $versions)) {
            $version = $versions[$options['version']];
            $return .= $this->script($version['url'], [
                'integrity' => $version['integrity'],
                'crossorigin' => 'anonymous'
            ]);
        }

        return $return;
    }

}
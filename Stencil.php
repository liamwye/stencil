<?php

/**
 * Part of the Stencil templating framework.
 *
 * @package  Stencil
 * @author   Liam Wye <me@liamwye.me>
 * @license  http://opensource.org/licenses/MIT The MIT license (MIT)
 * @version  0.3
 */
namespace Stencil;

/**
 * Simple Templating Engine designed to harness PHP as its templating language.
 *
 * PHP usage in templates can be streamlined using the PHP alternative syntax.
 *
 * @see http://www.php.net/manual/en/control-structures.alternative-syntax.php
 */
class Stencil implements Template
{
    /**
     * Name of the template.
     * @var string
     */
    protected $name;

    /**
     * Extension used for template files.
     * @var string
     */
    protected $extension = '.stencil.php';

    /**
     * Path to the main template directory.
     * @var string
     */
    protected $directory;

    /**
     * Filters to be applied to the template before and after rendering.
     * @var array
     */
    protected $templateFilters = array();

    /**
     * Filters to be applied to the variables before rendering.
     * @var array
     */
    protected $variableFilters = array();

    /**
     * Variables bound to the template.
     * @var array
     */
    protected $variables = array();

    /**
     * Scripts bound to the template.
     * These will be dynamically output to any script areas in the template.
     * @var array
     */
    protected $scripts = array();

    /**
     * Raw JS bound to the template.
     * This will be output within script tags to any script areas in the template.
     * @var array
     */
    protected $js = array();

    /**
     * Styles (CSS) bound to the template.
     * These will be dynamically output to any style areas in the template.
     * @var array
     */
    protected $styles = array();

    /**
     * Whether the template should inherit variables from parent templates.
     */
    protected $inherit;

    /**
     * Whether the template should be rendered with debug hinting or not.
     * @var boolean
     */
    protected $debug = true;

    /**
     * Set by the Router to define which route is currently active
     * @var string
     */
    protected $navigationActiveRoute = '/';

    /**
     * Initialise the template with some basic configuration.
     *
     * @param string  $name      Name of the template.
     * @param string  $directory Path to the template directory.
     * @param boolean $inherit   Whether the template should inherit variables
     *                           from parent templates.
     *
     * @return \Stencil\Stencil The Template object for fluidity.
     */
    public function __construct($name, $directory = 'templates/', $inherit = false)
    {
        // Init the stencil class
        $this->name = $name;
        $this->directory = $this->setDirectory($directory);
        $this->inherit = $this->setInheritance($inherit);

        return $this;
    }

    /**
     * Set the extension used for template files.
     *
     * @param string $extension Extension for template files.
     *
     * @return \Stencil\Stencil The Template object for fluidity.
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Set the path to the template directory.
     *
     * @param string $path Path to the template directory.
     *
     * @return \Stencil\Stencil The Template object for fluidity.
     */
    public function setDirectory($path)
    {
        $this->directory = $path;

        return $this;
    }

    /**
     * Set whether the template should inherit variables passed from parent
     * templates.
     *
     * @param boolean $inherit  Whether the template should inherit variables.
     *
     * @return \Stencil\Stencil The Template object for fluidity.
     */
    public function setInheritance($inherit)
    {
        $this->inherit = $inherit;

        return $this;
    }

    /**
     * Set a template variable.
     *
     * @param string $name  Name of the variable.
     * @param mixed  $value Value of the variable.
     *
     * @return \Stencil\Stencil The Template object for fluidity.
     */
    public function set($name, $value)
    {
        $this->variables[$name] = $value;

        return $this;
    }

    /**
     * Set an associative array as template variables.
     *
     * By utilising the $replace flag, you can have the array overwrite the
     * existing variables as opposed to merging them.
     *
     * @param array   $variables Associative array of name => value pairs.
     * @param boolean $replace   Whether the existing variables should be
     *                           replaced and overwritten.
     *
     * @return \Stencil\Stencil The Template object for fluidity.
     */
    public function setArray($variables, $replace = false)
    {
        // Check whether we want to replace the existing variables
        if ($replace) {
            $this->variables = $variables;
        } else {
            // Set each variable using set() so we can preserve any processing
            foreach ($variables as $key => $value) {
                $this->set($key, $value);
            }
        }

        return $this;
    }

    /**
     * Extend the template and create a child template.
     *
     * @param string  $identifier  Name to use to identify the template.
     * @param string  $name        Name of the template to be utilised, if null
     *                             value of identifier is used.
     * @param string  $directory   Path to the template directory, if null value
     *                             is inherited from parent.
     * @param boolean $inherit     Whether the template should inherit variables
     *                             from parent templates, if null value is inherited
     *                             from parent.
     *
     * @return \Stencil\Stencil    Instance of the newly created child template
     *                             or false if unable to complete.
     */
    public function extend($identifier, $name = null, $directory = null, $inherit = null)
    {
        // Define some default values (using the parent for reference)
        $name = (is_null($name) ? $identifier : $name);
        $directory = (is_null($directory) ? $this->directory : $directory);
        $inherit (is_null($inherit) ? $this->inherit : $inherit);

        // Define the template class that is to be created
        // To allow extension we will try to get the name of the called class
        $class = false;
        if (function_exists('get_called_class')) {
            $class = get_called_class();
        } else {
            $class = get_class($this);
        }

        // If we have a class name, instanciate it and bind it to the template
        if ($class !== false) {
            $template = new $class($name, $directory, $inherit);

            // Register the template with the parent (this)
            $this->set($identifier, $template);

            return $template;
        }

        return false;
    }

    /**
     * Get the name of the template.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the path to the template file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->directory . $this->name . $this->extension;
    }

    /**
     * Parse and render the template.
     *
     * @param mixed $variables Variables inherited from a parent template.
     *
     * @return string
     */
    public function render($variables = null)
    {
        // Check whether if we have variables that we're inheriting
        if (!is_null($variables) && $this->inherit) {
            $this->setArray($variables);
        }

        $path = $this->getPath();

        if (file_exists($path)) {
            // Pre-process the template variables
            $this->processTemplateVariables();

            // Load the template into a string and return it
            $template = $this->load($path);

            return $template;
        } else {
            throw new Stencil\StencilNotFoundException('Template file ' . $path . ' could not be found.');
        }
    }

    /**
     * Encapsulate the functionality required when extracting the contents of a
     * file using output buffering.
     *
     * This encapsulation allows pre and post processing to be applied to the
     * process and for this to be applied where ever it is utilised.
     *
     * @param string $path Path to the file to load.
     *
     * @return string The output from the file once loaded and processed.
     */
    protected function load($__path)
    {
        // Pre Process
        $this->preProcess();

        // Loop through the available variables and import them into the local
        // namespace
        foreach ($this->variables as $__key => $__variable) {
            // Check whether we're dealing with a child template that needs
            // to be rendered
            if ($__variable instanceof \Stencil\Stencil) {
                // We need to unset this child from the list of variables to
                // ensure we don't get stuck in a render loop
                unset($this->variables[$__key]);

                // Render the template so we can have the content
                $__variable = $__variable->render($this->variables);
            }

            $$__key = $__variable;
        }

        ob_start();         			 // Start the output buffering
        include ($__path);				 // Include the template file
        $__template = ob_get_contents(); // Get the template contents from the buffer
        ob_end_clean();					 // Tidy up

        // Apply any debugging (if defined within $this->debug)
        $__template = $this->debug($__template);

        // Post Process
        $__template = $this->postProcess($__template);

        return $__template;
    }

    /**
     * Apply debugging comments to the template data.
     * @param  string $template The template string.
     * @return string The template string with debugging comments.
     */
    protected function debug($template)
    {
        // Check whether we need to apply any debug hinting
        if ($this->debug) {
            // Show a different set of comments for empty templates
            if (empty($template)) {
                $template = PHP_EOL . '<!-- [Stencil]: Empty Stencil \'' . $this->name . '\' -->' . PHP_EOL;
            } else {
                $debugHeader = PHP_EOL . '<!-- [Stencil]: Start \'' . $this->name . '\' -->' . PHP_EOL;
                $debugFooter = PHP_EOL . '<!-- [Stencil]: End \'' . $this->name . '\' -->' . PHP_EOL;

                $template = $debugHeader  . $template . $debugFooter;
            }
        }

        return $template;
    }

    /**
     * Register a filter.
     *
     * @param mixed $filter The template filter to register.
     *
     * @return void
     */
    public function registerFilter($filter)
    {
        // Check whether we're registering a template or variable filter
        if ($filter instanceof \Stencil\Filter\TemplateFilter) {
            $this->templateFilters[] = $filter;
        } elseif ($filter instanceof \Stencil\Filter\IVariableFilter) {
            $this->variableFilters[] = $filter;
        }
    }

    /**
     * Process template variables.
     *
     * @return void
     */
    protected function processTemplateVariables()
    {
        // Check to ensure we have var filters to run
        if ((count($this->variableFilters) > 0)) {
            foreach ($this->variableFilters as $filter) {
                // Ensure the filter that has been registered is infact an
                // instance of IVariableFilter
                if ($filter instanceof \Stencil\Filter\IVariableFilter) {
                    // Filter!
                    $this->variables = $filter->process($this->variables);
                }
            }
        }
    }

    /**
     * Execute pre processing methods on registered template filters.
     *
     * @return void
     */
    protected function preProcess()
    {
        // Loop through the filters and execute post processing on the buffer
        foreach ($this->templateFilters as $filter) {
            if ($filter instanceof TemplateFilter) {
                $filter->preProcess();
            }
        }
    }

    /**
     * Execute post processing methods on registered template filters.
     *
     * @param string $buffer Buffer returned from the result of loading a
     *                       template file using output buffering.
     *
     * @return string
     */
    protected function postProcess($buffer)
    {
        // Loop through the filters and execute their post processing on the buffer
        foreach ($this->templateFilters as $filter) {
            if ($filter instanceof \Stencil\Filter\TemplateFilter) {
                $buffer = $filter->postProcess($buffer);
            }
        }

        return $buffer;
    }

    /**
     * Utility function to output variables cleanly within a template.
     * By passes the E_NOTICE warnings produced when a variable is not set.
     *
     * @param mixed $variable Variable name to print.
     * @param mixed $default  Default output if a variable is empty.
     */
    protected function show($variable, $default = '')
    {
        echo (isset($this->variables[$variable]) ? $this->variables[$variable] : $default);
    }

    /**
     * Utility function to output templates cleanly within a template.
     * By passes the E_NOTICE warnings produced when a template is not defined.
     *
     * @param mixed $template Template to embed.
     * @param mixed $default  Default output if a template is not defined (is empty).
     */
    protected function embed($template, $default = '')
    {
        echo (!empty($template) ? $template : $default);
    }

    /**
     * Set the active navigation route.
     * @param string $route The current active route.
     */
    public function setActiveRoute($route)
    {
        $this->navigationActiveRoute = $route;
    }

    /**
     * Utility function to render a navigation link to the provided route, with the
     * supplied text. This can optionally provide a condition that needs to be true
     * in order for the link to be rendered.
     *
     * @todo                     Implement some form of fuzzy/best match
     *                           comparison.
     *
     * @param string  $route     The route/link
     * @param string  $text      Text to use for the link
     * @param array   $classes   Classes to apply to the link
     * @param boolean $condition An optional condition. When true the link will render
     */
    protected function nav($route, $text, $classes = array(), $condition = true)
    {
        // Check whether this can be displayed
        if ($condition === true) {
            // Check whether this route is currently active
            $active = false;
            if ($route == $this->navigationActiveRoute) {
                $active = true;
            }

            // Build the link
            echo sprintf(
                '<li%s><a href="%s" title="%s"%s>%s</a>',
                (($active) ? ' class="active"' : ''),
                $route,
                $text,
                ((!empty($classes)) ? ' class="'.implode(' ', $classes).'"' : ''),
                $text
            );
        }
    }

    /**
     * Add raw JavaScript to be output to the stencil.
     *
     * @param string $script The raw JavaScript
     * @param int    $weight The weighting/order the JS should be output
     */
    public function addJs($script, $weight = null)
    {
        // Build the js array
        $js = array('js' => $script, 'src' => null);

        $this->addJsArray($js, $weight);
    }

    /**
     * Add a JavaScript script to the template.
     *
     * @param string $path   Path to the JS file.
     * @param string $weight A weight value for the file. This defines the order
     *                       in which the files are output to the browser.
     */
    public function addJsFile($path, $weight = null)
    {
        // Build the js array
        $js = array('js' => null, 'src' => $path);

        $this->addJsArray($js, $weight);
    }

    /**
     * Internal function to process the adding of script and raw JS to the stencil.
     *
     * @param array $js     An array of JS data (js and/or src)
     * @param int   $weight A weight value for the script tag to be output
     */
    protected function addJsArray($js, $weight)
    {
        // Convert the JS array into a StdObject
        $js = (object) $js;

        if (!in_array($js, $this->js)) {
            $weight = (is_null($weight) ? $this->jsNextWeight() : $weight);

            // Check if the weighted value already exists
            if (isset($this->js[$weight])) {
                // Convert the existing weight into a 3d array
                if (!is_array($this->js[$weight])) {
                    $this->js[$weight] = array($this->js[$weight]);
                }

                // Append the new js file to the array
                $this->js[$weight][] = $js;
            } else {
                $this->js[$weight] = $js;
            }
        }
    }

    /**
     * Return the next internal index for the JS array.
     * @return int Index of the next array index.
     */
    protected function jsNextWeight()
    {
        $keys = array_keys($this->js);
        $nextWeight = end($keys);

        return $nextWeight;
    }

    /**
     * Output all of the stored JS and script files to be rendered.
     */
    protected function getJs()
    {
        // Pre sort the array
        ksort($this->js);

        // Build the JS from the internal array
        // array(js, src)
        foreach ($this->js as $weight => $js) {
            // Check whether we're dealing with multiple items sharing the same weight
            if (is_array($js)) {
                foreach ($js as $j) {
                    echo $this->formatJs($j);
                }
            } else {
                echo $this->formatJs($js);
            }
        }
    }

    /**
     * Internal function to allow multidimensional weight arrays to share the same
     * JS processing. This will build the HTML to output for the script tag.
     *
     * @param  StdObject $js An object containing the JS script data
     * @return string    The HTML to output for the JS item.
     */
    protected function formatJs($js)
    {
        $script = '';

        // Check whether we're dealing with a file or raw js
        if (is_null($js->js) && !is_null($js->src)) {
            $script = sprintf('<script src="%s"></script>', $js->src);
        } else {
            // TODO: Do we want to concat all sequential raw js output and echo as one??
            $script = sprintf("<script>%s\t%s%s</script>", PHP_EOL, $js->js, PHP_EOL);
        }

         return PHP_EOL . $script . PHP_EOL;
    }

    public function addCss($path, $media = null)
    {
        $css = array('path' => $path, 'media' => $media);

        // Add the style to the array of styles
        if (!in_array($css, $this->styles)) {
            $this->styles[] = $css;
        }
    }

    public function getCss()
    {
        foreach ($this->styles as $css) {
            echo $this->parseCssArray($css);
        }
    }

    protected function parseCssArray($css)
    {
        $script = sprintf('<link href="%s" rel="stylesheet"', $css['path']);

        if (isset($css['media'])) {
            $script .= sprintf(' media="%s"', $css['media']);
        }

        $script .= ' />';

        return PHP_EOL . $script . PHP_EOL;
    }

    public function convertToString($input)
    {
        if (is_scalar($input)) {
            return (string) $input;
        } else {
            return '<pre>' . print_r($input, true) . '</pre>';
        }
    }

    public function toTable($data, $caption = null)
    {
        // Todo: Sort this path...
        $template = new Template('table', '/www/sites/planner/lib/vendor/Wye/Stencil/Templates/');
        $template->set('data', $data);

        // Check whether we need to define a caption
        if (!is_null($caption)) {
            $template->set('caption', $caption);
        }

        return $template->render();
    }
}

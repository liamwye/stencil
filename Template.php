<?php

/**
 * Part of the Stencil templating framework. A basic PHP templating library
 * for handling your templating requirements.
 *
 * @package Wye\Stencil
 * @author  Liam Wye <me@liamwye.me>
 * @license  http://opensource.org/licenses/MIT The MIT license (MIT)
 * @version  0.2.1
 */
namespace Wye\Stencil;

/**
 * Simple Templating Engine designed to harness PHP as its templating language.
 *
 * Opted against providing an application level cache in favour of utilising
 * HTML caching appropriately. An application level cache built-in to a
 * template framework would require a certain application structure, where as
 * an HTML cache class can be implemented far easier and deployed in any
 * structure.
 * PHP usage in templates can be streamlined using the PHP alternative syntax.
 *
 * @see http://www.php.net/manual/en/control-structures.alternative-syntax.php
 */
class Template implements ITemplate
{
    /**
     * Name of the template.
     * @var string
     */
    protected $templateName;

    /**
     * Extension used for template files.
     * @var string
     */
    protected $templateExtension = '.stencil.php';

    /**
     * Path to the main template directory.
     * @var string
     */
    protected $templateDirectory;

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
     * @param string  $templateName      Name of the template.
     * @param string  $templateDirectory Path to the template directory.
     * @param boolean $inherit           Whether the template should inherit
     *                                   variables from parent templates.
     *
     * @return \Wye\Stencil\ITemplate The Template object for fluidity.
     */
    public function __construct($templateName, $templateDirectory = 'templates/', $inherit = false)
    {
        # Basic initialisation
        $this->templateName = $templateName;
        $this->templateDirectory = $templateDirectory;
        $this->inherit = $inherit;

        return $this;
    }

    /**
     * Set a template variable.
     *
     * @param string $name  Name of the variable.
     * @param mixed  $value Value of the variable.
     *
     * @return \Wye\Stencil\ITemplate The Template object for fluidity.
     */
    public function set($name, $value)
    {
        # Add the data to the array of variables
        $this->variables[$name] = $value;

        return $this;
    }

    /**
     * Set an associative array as template variables.
     * By utilising the $overwrite flag, you can have the array replace the
     * existing variables as opposed to merging them.
     *
     * @param array   $variables Associative array of name => value pairs.
     * @param boolean $replace   Whether the existing variables should be
     *                           replaced and overwritten.
     *
     * @return \Wye\Stencil\ITemplate The Template object for fluidity.
     */
    public function setArray($variables, $replace = false)
    {
        # Check whether we want to replace the existing variables
        if ($replace) {
            $this->variables = $variables;
        } else {
            # Set each variable using set() so we can preserve any additional
            # processing, etc
            # Provides a single point of entry
            foreach ($variables as $key => $value) {
                $this->set($key, $value);
            }
        }

        return $this;
    }

    /**
     * Extend the template with a child template.
     *
     * @param string  $identifier        Name to use to identify the template.
     * @param string  $templateName      Name of the template to be utilised,
     *                                   if null value of identifier is
     *                                   used/duplicated.
     * @param string  $templateDirectory Path to the template directory, if null
     *                                   value is inherited from parent.
     * @param boolean $inherit           Whether the template should inherit
     *                                   variables from parent templates, if
     *                                   null value is inherited from parent.
     *
     * @return \Wye\Stencil\ITemplate Instance of the newly created child
     *                                template or false if unable to complete.
     */
    public function extend($identifier, $templateName = null, $templateDirectory = null, $inherit = null)
    {
        # Firstly, define the template directory
        if (is_null($templateDirectory)) {
            $templateDirectory = $this->templateDirectory;
        }
        # .. and template name
        if (is_null($templateName)) {
            $templateName = $identifier;
        }

        # Define the specific template class that is to be created
        # .. In order to allow for extension, we'll attempt to find the name of
        # the called class
        $class = false;

        if (function_exists('get_called_class')) {
            $class = get_called_class();
        } else {
            $class = $this->getCalledClass();
        }

        # If we have a class name, instanciate it and bind it to the template
        if ($class !== false) {
            # Define the arguments to pass
            if (is_null($templateDirectory)) {
                $templateDirectory = $this->templateDirectory;
            }
            if (is_null($inherit)) {
                $inherit = $this->inherit;
            }

            $template = new $class($templateName, $templateDirectory, $inherit);

            # Utilise the set method to add the template to take advantage of
            # any preprocessing, etc
            $this->set($identifier, $template);

            return $template;
        }

        return false;
    }

    /**
     * Ugly implementation of get_called_class where not available.
     * Returns the class that called the function. By this we mean the class
     * which called the function in which getCalledClass was called.
     *
     * @return string The class that called the function.
     */
    protected function getCalledClass()
    {
        # Implement an "ugly" workaround to apply similar functionality
        # to get_called_class
        $backtrace = debug_backtrace();
        if (isset($backtrace[0])) {
            $instace = $backtrace[0]; # Define the instance of the backtrace

            # Check to see if we can narrow down to finding the class name
            if (array_key_exists('object', $instance)) {
                if (array_key_exists('class', $instance)) {
                    if ($instance['object'] instanceof $instance['class']) {
                        $class = get_class($instance['object']);

                        return $class;
                    }
                }
            }
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
        return $this->templateName;
    }

    /**
     * Get the path to the template file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->templateDirectory . $this->templateName . $this->templateExtension;
    }

    /**
     * Get the path to the file to be rendered.
     *
     * @return string
     */
    protected function getRenderPath()
    {
        # By default return the template path
        # .. Eventually can be overriden to supply paths to caches, etc

        return $this->getPath();
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
        # Check whether we're inheriting variables
        # .. Ensure we have vars and this template is set to inherit variables
        # from other sources
        if (!is_null($variables) && $this->inherit) {
            $this->setArray($variables);
        }

        # Contine with rendering the template...
        if (file_exists($this->getRenderPath())) {
            # Run any processing required for the template variables
            $this->processTemplateVariables();

            # Load the template into a string and return it
            $template = $this->load($this->getRenderPath());

            return $template;
        } else {
            throw new \Exception('Template file ' . $this->getPath() . ' could not be found.');
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
        # Pre Process
        $this->preProcess();

        # Loop through the available variables and import them into the local
        # namespace
        foreach ($this->variables as $__key => $__variable) {
            # Check whether we're dealing with a child template that needs
            # to be rendered
            if ($__variable instanceof \Wye\Stencil\ITemplate) {
                # We need to unset this child from the list of variables to
                # ensure we don't get stuck in a render loop
                unset($this->variables[$__key]);

                # Render the template so we can have the content
                $__variable = $__variable->render($this->variables);
            }

            $$__key = $__variable;
        }

        ob_start();         			 # Start the output buffering
        include ($__path);				 # Include the template file
        $__template = ob_get_contents(); # Get the template contents from the buffer
        ob_end_clean();					 # Tidy up

        # Apply any debugging (if defined within $this->debug)
        $__template = $this->debug($__template);

        # Post Process
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
        # Check whether we need to apply any debug hinting
        if ($this->debug) {
            # Show a different set of comments for empty templates
            if (empty($template)) {
                $template = PHP_EOL . '<!-- [Stencil]: Empty Stencil \'' . $this->templateName . '\' -->' . PHP_EOL;
            } else {
                $debugHeader = PHP_EOL . '<!-- [Stencil]: Start \'' . $this->templateName . '\' -->' . PHP_EOL;
                $debugFooter = PHP_EOL . '<!-- [Stencil]: End \'' . $this->templateName . '\' -->' . PHP_EOL;

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
        # Check whether we're registering a template or variable filter
        if ($filter instanceof \Wye\Stencil\Filter\ITemplateFilter) {
            $this->templateFilters[] = $filter;
        } elseif ($filter instanceof \Wye\Stencil\Filter\IVariableFilter) {
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
        # Check to ensure we have var filters to run
        if ((count($this->variableFilters) > 0)) {
            foreach ($this->variableFilters as $filter) {
                # Ensure the filter that has been registered is infact an
                # instance of IVariableFilter
                if ($filter instanceof \Wye\Stencil\Filter\IVariableFilter) {
                    # Filter!
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
        # Loop through the filters and execute post processing on the buffer
        foreach ($this->templateFilters as $filter) {
            if ($filter instanceof ITemplateFilter) {
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
        # Loop through the filters and execute their post processing on the buffer
        foreach ($this->templateFilters as $filter) {
            if ($filter instanceof \Wye\Stencil\Filter\ITemplateFilter) {
                $buffer = $filter->postProcess($buffer);
            }
        }

        return $buffer;
    }

    /**
     * Set the extension used for template files.
     *
     * @param string $extension Extension for template files.
     *
     * @return \Wye\Stencil\ITemplate The Template object for fluidity.
     */
    public function setExtension($extension)
    {
        $this->templateExtension = $extension;

        return $this;
    }

    /**
     * Set the path to the template directory.
     *
     * @param string $path Path to the template directory.
     *
     * @return \Wye\Stencil\ITemplate The Template object for fluidity.
     */
    public function setTemplateDirectory($path)
    {
        $this->templateDirectory = $path;

        return $this;
    }

    /**
     * Set whether the template should inherit variables passed from parent templates.
     *
     * @param boolean $inherit Whether the template should inherit variables.
     *
     * @return \Wye\Stencil\ITemplate The Template object for fluidity.
     */
    public function setInheritance($inherit)
    {
        $this->inherit = $inherit;

        return $this;
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
        # Check whether this can be displayed
        if ($condition === true) {
            # Check whether this route is currently active
            $active = false;
            if ($route == $this->navigationActiveRoute) {
                $active = true;
            }

            # Build the link
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
        # Build the js array
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
        # Build the js array
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
        # Convert the JS array into a StdObject
        $js = (object) $js;

        if (!in_array($js, $this->js)) {
            $weight = (is_null($weight) ? $this->jsNextWeight() : $weight);

            # Check if the weighted value already exists
            if (isset($this->js[$weight])) {
                # Convert the existing weight into a 3d array
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
        
        # Build the JS from the internal array
        # array(js, src)
        foreach ($this->js as $weight => $js) {
            # Check whether we're dealing with multiple items sharing the same weight
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

        # Check whether we're dealing with a file or raw js
        if (is_null($js->js) && !is_null($js->src)) {
            $script = sprintf('<script src="%s"></script>', $js->src);
        } else {
            # TODO: Do we want to concat all sequential raw js output and echo as one??
            $script = sprintf("<script>%s\t%s%s</script>", PHP_EOL, $js->js, PHP_EOL);
        }

         return PHP_EOL . $script . PHP_EOL;
    }

    public function addCss($path, $media = null)
    {
        $css = array('path' => $path, 'media' => $media);

        # Add the style to the array of styles
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

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
 * Simple template designed to harness PHP as its templating language.
 *
 * PHP usage in templates can be streamlined using the PHP alternative syntax.
 *
 * @see http://www.php.net/manual/en/control-structures.alternative-syntax.php
 */
class Template implements TemplateInterface
{
    /**
     * Name of the template. Used as a reference to access the template.
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
     * Whether the template should inherit variables from parent templates.
     */
    protected $inherit;

    /**
     * Whether the template should be rendered with debug hinting or not.
     * @var boolean
     */
    protected $debug = true;

    /**
     * Initialise the template with some basic configuration.
     *
     * @param string  $name      Name of the template.
     * @param string  $directory Path to the template directory.
     * @param boolean $inherit   Whether the template should inherit variables
     *                           from parent templates.
     *
     * @return \Stencil\Template The Template object for fluidity.
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
     * @return \Stencil\Template The Template object for fluidity.
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
     * @return \Stencil\Template The Template object for fluidity.
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
     * @return \Stencil\Template The Template object for fluidity.
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
     * @return \Stencil\Template The Template object for fluidity.
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
     * @return \Stencil\Template The Template object for fluidity.
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
     * @return \Stencil\Template    Instance of the newly created child template
     *                             or false if unable to complete.
     */
    public function extend($identifier, $name = null, $directory = null, $inherit = null)
    {
        // Define some default values (using the parent for reference)
        $name = (is_null($name) ? $identifier : $name);
        $directory = (is_null($directory) ? $this->directory : $directory);
        $inherit (is_null($inherit) ? $this->inherit : $inherit);

        // To allow extension we will try to get the name of the called class
        try {
            // Instanciate the child and add it to the parent template
            $template = new \Stencil\Template($name, $directory, $inherit);
            $this->set($identifier, $template);

            return $template;
        } catch (\Exception $e) {
            return false;
        }
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
            // Load the template into a string and return it
            $template = $this->load($path);

            return $template;
        } else {
            throw new Stencil\TemplateNotFoundException('Template file ' . $path . ' could not be found.');
        }
    }

    /**
     * Encapsulate the functionality required when extracting the contents of a
     * file using output buffering.
     *
     * This encapsulation allows pre and post processing to be applied to the
     * process.
     *
     * @param string $path Path to the file to load.
     *
     * @return string      The output from the file once loaded and processed.
     */
    protected function load($__path)
    {
        // Pre Process
        $this->preProcess();


        // Pre-process the template variables
        $this->preProcessVariables();

        // Loop through template variables and import them into local namespace
        foreach ($this->variables as $__key => $__variable) {
            // Check this is a child template that needs to be rendered
            if ($__variable instanceof \Stencil\TemplateInterface) {
                // Unset the child to prevent a render loop
                unset($this->variables[$__key]);

                // Render the template [recursive]
                $__variable = $__variable->render($this->variables);
            }

            $$__key = $__variable;
        }

        // Have stencil check the rendering process for PHP errors.
        // This is largely to avoid E_NOTICEs from variables that don't exist.
        set_error_handler(array($this, 'handleRenderErrors'));

        try {
            ob_start();                      // Start the output buffering
            include ($__path);               // Include the template file
            $__template = ob_get_contents(); // Get the template contents from the buffer
            ob_end_clean();                  // Tidy up
        } catch (\ErrorException $e) {
            // TODO: Investigate whether we can identify and handle and missing variables
        }

        // Remove the render error handler
        restore_error_handler();

        // Apply any debugging (if defined within $this->debug)
        $__template = $this->debug($__template);

        // Post Process
        $__template = $this->postProcess($__template);

        return $__template;
    }

    protected function handleRenderErrors($errorNo, $errorStr, $errorFile, $errorLine) {
        // Throw an ErrorException
        throw new ErrorException($errorStr, 0, $errorNo, $errorFile, $errorLine);

        return false;
    }

    /**
     * Apply debugging comments to the template data.
     *
     * @param  string $template The template string.
     * @return string           The template string with debugging comments.
     */
    protected function debug($template)
    {
        // Check whether we need to apply any debug hinting
        if ($this->debug) {
            // Show a different set of comments for empty templates
            if (empty($template)) {
                $template = PHP_EOL . '<!-- [Stencil]: Empty Stencil \'' . $this->name . '\' -->' . PHP_EOL;
            } else {
                $template = PHP_EOL . '<!-- [Stencil]: Start \'' . $this->name . '\' -->' . PHP_EOL
                  . $template
                  . PHP_EOL . '<!-- [Stencil]: End \'' . $this->name . '\' -->' . PHP_EOL;
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
        } elseif ($filter instanceof \Stencil\Filter\VariableFilterInterface) {
            $this->variableFilters[] = $filter;
        }
    }

    /**
     * Execute pre processing methods on registered template filters.
     *
     * @return void
     */
    protected function preProcess()
    {
        // Loop through the filters and execute post processing
        foreach ($this->templateFilters as $filter) {
            if ($filter instanceof \Stencil\Filter\TemplateFilterInterface) {
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
            if ($filter instanceof \Stencil\Filter\TemplateFilterInterface) {
                $buffer = $filter->postProcess($buffer);
            }
        }

        return $buffer;
    }

    /**
     * Process template variables.
     *
     * @return void
     */
    protected function preProcessVariables()
    {
        // Check to ensure we have var filters to run
        if ((count($this->variableFilters) > 0)) {
            foreach ($this->variableFilters as $filter) {
                // Ensure the filter that has been registered is infact an
                // instance of IVariableFilter
                if ($filter instanceof \Stencil\Filter\VariableFilterInterface) {
                    // Filter!
                    $this->variables = $filter->process($this->variables);
                }
            }
        }
    }
}

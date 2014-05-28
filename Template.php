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
class Template extends \Stencil\Observer\Observable implements TemplateInterface
{
    /**
     * The name used to identify the template.
     * This is especially relevant when the template is extended from a parent.
     * @var string
     */
    protected $identifier;

    /**
     * An array of Template configuration values.
     * @var array
     */
    protected $configuration = array();

    /**
     * Variables bound to the template.
     * @var array
     */
    protected $variables = array();

    /**
     * Initialise the template with some basic configuration.
     *
     * @param string  $identifier  Name used to identify the template.
     * @param array   $config      An array of configuration key value pairs.
     *
     * @return \Stencil\Template The Template object for fluidity.
     */
    public function __construct(String $identifier, Array $config = array())
    {
        $this->identifier = $identifier;

        // Check that we have a path value present
        // This is a mandatory value as we need to know where the template file is
        if (!array_key_exists('path', $config) || empty($config['path'])) {
            throw new \Stencil\Exceptions\StencilNotFoundException('No Stencil path given.');
        }

        // Define the configuration
        $this->configuration = $config;

        return $this;
    }

    public function __call($method, $parameters = array()) {
        // Get the prefix; get/set
        $prefix = strtolower(substr($method, 0, 3));

        // Get the rest of the method name; the key
        $key = strtolower(substr($method, 3));

        // Handle the call according to the prefix
        if ($prefix === 'get') {
            $result = false;
            if (array_key_exists($key, $this->configuration)) {
                $result = $this->configuration[$key];
            } elseif ($key === 'identifier') {
                $result = $this->identifier;
            }

            return $result;
        } elseif ($prefix === 'set') {
            // Check for the value
            if (count($parameters) > 0) {
                $value = array_shift($parameters);

                // Run some additional processing for paths
                if ($key === 'path') {
                    if (!file_exists($value)) {
                        throw new \Stencil\Exceptions\StencilNotFoundException('Stencil
                            could not be found.');
                    }
                }

                // Set the value
                if ($key === 'identifier') {
                    $this->identifier = $value;
                } else {
                    $this->configuration[$key] = $value;
                }

                // Return the object for fluidity
                return $this;
            }
        }

        throw new BadMethodCallException('Call to undefined method.');
    }

    /**
     * Set a template variable.
     *
     * @param string $name  Name of the variable.
     * @param mixed  $value Value of the variable.
     *
     * @return \Stencil\Template The Template object for fluidity.
     */
    public function set(String $name, $value)
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
    public function setArray(Array $variables, Boolean $replace = false)
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
     * @param array   $config      An array of configuration key value pairs.
     *
     * @return \Stencil\Template    Instance of the newly created child template
     *                             or false if unable to complete.
     */
    public function extend(String $identifier, Array $config = array())
    {
        // Merge the existing configuration with what we have been passed
        // Existing values will be overwritten if new values are passed
        $config = array_merge($this->configuration, $config);

        // To allow extension we will try to get the name of the called class
        $template = false;
        try {
            // Instanciate the child and add it to the parent template
            $template = new \Stencil\Template($identifier, $config);

            $this->set($identifier, $template);
        } catch (\Exception $e) {
            $template = false;
        }

        return $template;
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
     * Note: Internal variables are prefixed with '__' to attempt to avoid clashes
     * in the local namespace.
     *
     * @param string $path Path to the file to load.
     * @return string      The output from the file once loaded and processed.
     */
    protected function load($__path)
    {
        // Define a base context for event dispatching
        $context = array(
            'identifier'    => $this->identifier,
            'configuration' => &$this->configuration,
            'variables'     => &$this->variables,
        );

        // Pre Process
        $this->dispatch('Template_PreProcess', $context);

        // Pre-process the template variables
        $this->dispatch('Variables_PreProcess', $context);

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
            // Todo: Investigate whether we can identify and handle and missing variables
        }

        // Remove the render error handler
        restore_error_handler();

        // Add the template content to the context
        $context['template'] = &$template;

        // Post Process
        $this->dispatch('Template_PostProcess', $context);

        return $__template;
    }

    /**
     * Implementation for set_error_handler(). This will allow us to convert runtime
     * errors into PHP ErrorExceptions - these can then be caught and handled where
     * appropriate.
     *
     * @see    set_error_handler()
     * @see    ErrorException
     *
     * @param  Int    $errorNo   The error number.
     * @param  String $errorStr  The error string.
     * @param  String $errorFile The error file.
     * @param  Int    $errorLine The error line.
     * @return Boolean           Always false.
     */
    protected function handleRenderErrors($errorNo, $errorStr, $errorFile, $errorLine) {
        // Throw an ErrorException
        throw new ErrorException($errorStr, 0, $errorNo, $errorFile, $errorLine);

        return false;
    }
}

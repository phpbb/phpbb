<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader;

use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\ExpressionLanguage\Expression;

/**
 * YamlFileLoader loads YAML files service definitions.
 *
 * The YAML format does not support anonymous services (cf. the XML loader).
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class YamlFileLoader extends FileLoader
{
    private $yamlParser;

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        $path = $this->locator->locate($resource);

        $content = $this->loadFile($path);

        $this->container->addResource(new FileResource($path));

        // empty file
        if (null === $content) {
            return;
        }

        // imports
        $this->parseImports($content, $path);

        // parameters
        if (isset($content['parameters'])) {
            if (!is_array($content['parameters'])) {
                throw new InvalidArgumentException(sprintf('The "parameters" key should contain an array in %s. Check your YAML syntax.', $resource));
            }

            foreach ($content['parameters'] as $key => $value) {
                $this->container->setParameter($key, $this->resolveServices($value));
            }
        }

        // extensions
        $this->loadFromExtensions($content);

        // services
        $this->parseDefinitions($content, $resource);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && in_array(pathinfo($resource, PATHINFO_EXTENSION), array('yml', 'yaml'), true);
    }

    /**
     * Parses all imports.
     *
     * @param array  $content
     * @param string $file
     */
    private function parseImports(array $content, $file)
    {
        if (!isset($content['imports'])) {
            return;
        }

        if (!is_array($content['imports'])) {
            throw new InvalidArgumentException(sprintf('The "imports" key should contain an array in %s. Check your YAML syntax.', $file));
        }

        $defaultDirectory = dirname($file);
        foreach ($content['imports'] as $import) {
            if (!is_array($import)) {
                throw new InvalidArgumentException(sprintf('The values in the "imports" key should be arrays in %s. Check your YAML syntax.', $file));
            }

            $this->setCurrentDir($defaultDirectory);
            $this->import($import['resource'], null, isset($import['ignore_errors']) ? (bool) $import['ignore_errors'] : false, $file);
        }
    }

    /**
     * Parses definitions.
     *
     * @param array  $content
     * @param string $file
     */
    private function parseDefinitions(array $content, $file)
    {
        if (!isset($content['services'])) {
            return;
        }

        if (!is_array($content['services'])) {
            throw new InvalidArgumentException(sprintf('The "services" key should contain an array in %s. Check your YAML syntax.', $file));
        }

        foreach ($content['services'] as $id => $service) {
            $this->parseDefinition($id, $service, $file);
        }
    }

    /**
     * Parses a definition.
     *
     * @param string       $id
     * @param array|string $service
     * @param string       $file
     *
     * @throws InvalidArgumentException When tags are invalid
     */
    private function parseDefinition($id, $service, $file)
    {
        if (is_string($service) && 0 === strpos($service, '@')) {
            $this->container->setAlias($id, substr($service, 1));

            return;
        }

        if (!is_array($service)) {
            throw new InvalidArgumentException(sprintf('A service definition must be an array or a string starting with "@" but %s found for service "%s" in %s. Check your YAML syntax.', gettype($service), $id, $file));
        }

        if (isset($service['alias'])) {
            $public = !array_key_exists('public', $service) || (bool) $service['public'];
            $this->container->setAlias($id, new Alias($service['alias'], $public));

            return;
        }

        if (isset($service['parent'])) {
            $definition = new DefinitionDecorator($service['parent']);
        } else {
            $definition = new Definition();
        }

        if (isset($service['class'])) {
            $definition->setClass($service['class']);
        }

        if (isset($service['shared'])) {
            $definition->setShared($service['shared']);
        }

        if (isset($service['scope'])) {
            if ('request' !== $id) {
                @trigger_error(sprintf('The "scope" key of service "%s" in file "%s" is deprecated since version 2.8 and will be removed in 3.0.', $id, $file), E_USER_DEPRECATED);
            }
            $definition->setScope($service['scope'], false);
        }

        if (isset($service['synthetic'])) {
            $definition->setSynthetic($service['synthetic']);
        }

        if (isset($service['synchronized'])) {
            @trigger_error(sprintf('The "synchronized" key of service "%s" in file "%s" is deprecated since version 2.7 and will be removed in 3.0.', $id, $file), E_USER_DEPRECATED);
            $definition->setSynchronized($service['synchronized'], 'request' !== $id);
        }

        if (isset($service['lazy'])) {
            $definition->setLazy($service['lazy']);
        }

        if (isset($service['public'])) {
            $definition->setPublic($service['public']);
        }

        if (isset($service['abstract'])) {
            $definition->setAbstract($service['abstract']);
        }

        if (array_key_exists('deprecated', $service)) {
            $definition->setDeprecated(true, $service['deprecated']);
        }

        if (isset($service['factory'])) {
            if (is_string($service['factory'])) {
                if (false !== strpos($service['factory'], ':') && false === strpos($service['factory'], '::')) {
                    $parts = explode(':', $service['factory']);
                    $definition->setFactory(array($this->resolveServices('@'.$parts[0]), $parts[1]));
                } else {
                    $definition->setFactory($service['factory']);
                }
            } else {
                $definition->setFactory(array($this->resolveServices($service['factory'][0]), $service['factory'][1]));
            }
        }

        if (isset($service['factory_class'])) {
            @trigger_error(sprintf('The "factory_class" key of service "%s" in file "%s" is deprecated since version 2.6 and will be removed in 3.0. Use "factory" instead.', $id, $file), E_USER_DEPRECATED);
            $definition->setFactoryClass($service['factory_class']);
        }

        if (isset($service['factory_method'])) {
            @trigger_error(sprintf('The "factory_method" key of service "%s" in file "%s" is deprecated since version 2.6 and will be removed in 3.0. Use "factory" instead.', $id, $file), E_USER_DEPRECATED);
            $definition->setFactoryMethod($service['factory_method']);
        }

        if (isset($service['factory_service'])) {
            @trigger_error(sprintf('The "factory_service" key of service "%s" in file "%s" is deprecated since version 2.6 and will be removed in 3.0. Use "factory" instead.', $id, $file), E_USER_DEPRECATED);
            $definition->setFactoryService($service['factory_service']);
        }

        if (isset($service['file'])) {
            $definition->setFile($service['file']);
        }

        if (isset($service['arguments'])) {
            $definition->setArguments($this->resolveServices($service['arguments']));
        }

        if (isset($service['properties'])) {
            $definition->setProperties($this->resolveServices($service['properties']));
        }

        if (isset($service['configurator'])) {
            if (is_string($service['configurator'])) {
                $definition->setConfigurator($service['configurator']);
            } else {
                $definition->setConfigurator(array($this->resolveServices($service['configurator'][0]), $service['configurator'][1]));
            }
        }

        if (isset($service['calls'])) {
            if (!is_array($service['calls'])) {
                throw new InvalidArgumentException(sprintf('Parameter "calls" must be an array for service "%s" in %s. Check your YAML syntax.', $id, $file));
            }

            foreach ($service['calls'] as $call) {
                if (isset($call['method'])) {
                    $method = $call['method'];
                    $args = isset($call['arguments']) ? $this->resolveServices($call['arguments']) : array();
                } else {
                    $method = $call[0];
                    $args = isset($call[1]) ? $this->resolveServices($call[1]) : array();
                }

                $definition->addMethodCall($method, $args);
            }
        }

        if (isset($service['tags'])) {
            if (!is_array($service['tags'])) {
                throw new InvalidArgumentException(sprintf('Parameter "tags" must be an array for service "%s" in %s. Check your YAML syntax.', $id, $file));
            }

            foreach ($service['tags'] as $tag) {
                if (!is_array($tag)) {
                    throw new InvalidArgumentException(sprintf('A "tags" entry must be an array for service "%s" in %s. Check your YAML syntax.', $id, $file));
                }

                if (!isset($tag['name'])) {
                    throw new InvalidArgumentException(sprintf('A "tags" entry is missing a "name" key for service "%s" in %s.', $id, $file));
                }

                if (!is_string($tag['name']) || '' === $tag['name']) {
                    throw new InvalidArgumentException(sprintf('The tag name for service "%s" in %s must be a non-empty string.', $id, $file));
                }

                $name = $tag['name'];
                unset($tag['name']);

                foreach ($tag as $attribute => $value) {
                    if (!is_scalar($value) && null !== $value) {
                        throw new InvalidArgumentException(sprintf('A "tags" attribute must be of a scalar-type for service "%s", tag "%s", attribute "%s" in %s. Check your YAML syntax.', $id, $name, $attribute, $file));
                    }
                }

                $definition->addTag($name, $tag);
            }
        }

        if (isset($service['decorates'])) {
            if ('' !== $service['decorates'] && '@' === $service['decorates'][0]) {
                throw new InvalidArgumentException(sprintf('The value of the "decorates" option for the "%s" service must be the id of the service without the "@" prefix (replace "%s" with "%s").', $id, $service['decorates'], substr($service['decorates'], 1)));
            }

            $renameId = isset($service['decoration_inner_name']) ? $service['decoration_inner_name'] : null;
            $priority = isset($service['decoration_priority']) ? $service['decoration_priority'] : 0;
            $definition->setDecoratedService($service['decorates'], $renameId, $priority);
        }

        if (isset($service['autowire'])) {
            $definition->setAutowired($service['autowire']);
        }

        if (isset($service['autowiring_types'])) {
            if (is_string($service['autowiring_types'])) {
                $definition->addAutowiringType($service['autowiring_types']);
            } else {
                if (!is_array($service['autowiring_types'])) {
                    throw new InvalidArgumentException(sprintf('Parameter "autowiring_types" must be a string or an array for service "%s" in %s. Check your YAML syntax.', $id, $file));
                }

                foreach ($service['autowiring_types'] as $autowiringType) {
                    if (!is_string($autowiringType)) {
                        throw new InvalidArgumentException(sprintf('A "autowiring_types" attribute must be of type string for service "%s" in %s. Check your YAML syntax.', $id, $file));
                    }

                    $definition->addAutowiringType($autowiringType);
                }
            }
        }

        $this->container->setDefinition($id, $definition);
    }

    /**
     * Loads a YAML file.
     *
     * @param string $file
     *
     * @return array The file content
     *
     * @throws InvalidArgumentException when the given file is not a local file or when it does not exist
     */
    protected function loadFile($file)
    {
        if (!class_exists('Symfony\Component\Yaml\Parser')) {
            throw new RuntimeException('Unable to load YAML config files as the Symfony Yaml Component is not installed.');
        }

        if (!stream_is_local($file)) {
            throw new InvalidArgumentException(sprintf('This is not a local file "%s".', $file));
        }

        if (!file_exists($file)) {
            throw new InvalidArgumentException(sprintf('The file "%s" does not exist.', $file));
        }

        if (null === $this->yamlParser) {
            $this->yamlParser = new YamlParser();
        }

        try {
            $configuration = $this->yamlParser->parse(file_get_contents($file));
        } catch (ParseException $e) {
            throw new InvalidArgumentException(sprintf('The file "%s" does not contain valid YAML.', $file), 0, $e);
        }

        return $this->validate($configuration, $file);
    }

    /**
     * Validates a YAML file.
     *
     * @param mixed  $content
     * @param string $file
     *
     * @return array
     *
     * @throws InvalidArgumentException When service file is not valid
     */
    private function validate($content, $file)
    {
        if (null === $content) {
            return $content;
        }

        if (!is_array($content)) {
            throw new InvalidArgumentException(sprintf('The service file "%s" is not valid. It should contain an array. Check your YAML syntax.', $file));
        }

        foreach ($content as $namespace => $data) {
            if (in_array($namespace, array('imports', 'parameters', 'services'))) {
                continue;
            }

            if (!$this->container->hasExtension($namespace)) {
                $extensionNamespaces = array_filter(array_map(function ($ext) { return $ext->getAlias(); }, $this->container->getExtensions()));
                throw new InvalidArgumentException(sprintf(
                    'There is no extension able to load the configuration for "%s" (in %s). Looked for namespace "%s", found %s',
                    $namespace,
                    $file,
                    $namespace,
                    $extensionNamespaces ? sprintf('"%s"', implode('", "', $extensionNamespaces)) : 'none'
                ));
            }
        }

        return $content;
    }

    /**
     * Resolves services.
     *
     * @param string|array $value
     *
     * @return array|string|Reference
     */
    private function resolveServices($value)
    {
        if (is_array($value)) {
            $value = array_map(array($this, 'resolveServices'), $value);
        } elseif (is_string($value) && 0 === strpos($value, '@=')) {
            return new Expression(substr($value, 2));
        } elseif (is_string($value) && 0 === strpos($value, '@')) {
            if (0 === strpos($value, '@@')) {
                $value = substr($value, 1);
                $invalidBehavior = null;
            } elseif (0 === strpos($value, '@?')) {
                $value = substr($value, 2);
                $invalidBehavior = ContainerInterface::IGNORE_ON_INVALID_REFERENCE;
            } else {
                $value = substr($value, 1);
                $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
            }

            if ('=' === substr($value, -1)) {
                $value = substr($value, 0, -1);
                $strict = false;
            } else {
                $strict = true;
            }

            if (null !== $invalidBehavior) {
                $value = new Reference($value, $invalidBehavior, $strict);
            }
        }

        return $value;
    }

    /**
     * Loads from Extensions.
     */
    private function loadFromExtensions(array $content)
    {
        foreach ($content as $namespace => $values) {
            if (in_array($namespace, array('imports', 'parameters', 'services'))) {
                continue;
            }

            if (!is_array($values)) {
                $values = array();
            }

            $this->container->loadFromExtension($namespace, $values);
        }
    }
}
